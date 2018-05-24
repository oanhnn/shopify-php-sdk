<?php

namespace Shopify\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shopify\Exception\ApiLimitExceedException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Retry implements Plugin
{
    /**
     * Number of retry before sending an exception.
     *
     * @var int
     */
    private $retry;

    /**
     * @var callable
     */
    private $delay;

    /**
     * @var callable
     */
    private $decider;

    /**
     * Store the retry counter for each request.
     *
     * @var array
     */
    private $retryStorage = [];

    /**
     * @param array $config {
     * @var int $retries Number of retries to attempt if an exception occurs before letting the exception bubble up.
     * @var callable $decider A callback that gets a request and an exception to decide after a failure whether the request should be retried.
     * @var callable $delay A callback that gets a request, an exception and the number of retries and returns how many microseconds we should wait before trying again.
     * }
     */
    public function __construct(array $config = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'retries' => 1,
            'decider' => __CLASS__ . '::defaultDecider',
            'delay' => __CLASS__ . '::defaultDelay',
        ]);
        $resolver->setAllowedTypes('retries', 'int');
        $resolver->setAllowedTypes('decider', 'callable');
        $resolver->setAllowedTypes('delay', 'callable');
        $options = $resolver->resolve($config);

        $this->retry = $options['retries'];
        $this->decider = $options['decider'];
        $this->delay = $options['delay'];
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $chainIdentifier = spl_object_hash((object)$first);

        return $next($request)->then(function (ResponseInterface $response) use ($request, $chainIdentifier) {
            if (array_key_exists($chainIdentifier, $this->retryStorage)) {
                unset($this->retryStorage[$chainIdentifier]);
            }

            return $response;
        }, function (Exception $exception) use ($request, $next, $first, $chainIdentifier) {
            if (!array_key_exists($chainIdentifier, $this->retryStorage)) {
                $this->retryStorage[$chainIdentifier] = 0;
            }

            // If retries greater than or equal to max retry, throw exception
            if ($this->retryStorage[$chainIdentifier] >= $this->retry) {
                unset($this->retryStorage[$chainIdentifier]);

                throw $exception;
            }

            if (!call_user_func($this->decider, $request, $exception)) {
                throw $exception;
            }

            $time = call_user_func($this->delay, $request, $exception, $this->retryStorage[$chainIdentifier]);
            usleep($time);

            // Retry in synchrone
            ++$this->retryStorage[$chainIdentifier];
            $promise = $this->handleRequest($request, $next, $first);

            return $promise->wait();
        });
    }

    /**
     * @param RequestInterface $request
     * @param Exception $exception
     * @param int $retries The number of retries we made before. First time this get called it will be 0.
     * @return int The microseconds
     */
    public static function defaultDelay(RequestInterface $request, Exception $exception, $retries): int
    {
        // First time, not delay
        if ($retries === 0) {
            return 0;
        }

        // If exception is ApiLimitExceedException, delay time is reset time
        if (method_exists($exception, 'getResetTime')) {
            return $exception->getResetTime() + 1;
        }

        return 100 * rand(1, 10);
    }

    /**
     * @param RequestInterface $request
     * @param Exception $e
     * @return bool
     */
    public static function defaultDecider(RequestInterface $request, Exception $e): bool
    {
        return $e instanceof ApiLimitExceedException;
    }
}
