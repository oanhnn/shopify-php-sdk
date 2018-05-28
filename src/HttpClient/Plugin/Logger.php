<?php

namespace Http\Client\Common\Plugin;

use Http\Client\Common\Plugin;
use Http\Client\Exception;
use Http\Client\Exception\HttpException;
use Http\Message\Formatter;
use Http\Message\Formatter\SimpleFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Log request, response and exception for an HTTP Client.
 */
final class Logger implements Plugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * LoggerPlugin constructor.
     * @param LoggerInterface $logger
     * @param Formatter|null $formatter
     */
    public function __construct(LoggerInterface $logger, Formatter $formatter = null)
    {
        $this->logger = $logger;
        $this->formatter = $formatter ?: new SimpleFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $start = microtime(true);
        $this->logger->info(
            sprintf(
                "Sending request:\n%s",
                $this->formatter->formatRequest($request)
            ),
            [
                'request' => $request
            ]
        );

        return $next($request)->then(function (ResponseInterface $response) use ($request, $start) {
            $milliseconds = (int)round((microtime(true) - $start) * 1000);

            $this->logError($request, $response, $milliseconds);

            return $response;
        }, function (Exception $exception) use ($request, $start) {
            $milliseconds = (int)round((microtime(true) - $start) * 1000);

            $this->logError($request, $exception, $milliseconds);

            throw $exception;
        });
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param int $time
     */
    protected function logInfor(RequestInterface $request, ResponseInterface $response, $time)
    {
        $this->logger->info(
            sprintf(
                "Received response:\n%s\n\nfor request:\n%s",
                $this->formatter->formatResponse($response),
                $this->formatter->formatRequest($request)
            ),
            [
                'request' => $request,
                'response' => $response,
                'milliseconds' => $time,
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param Exception $exception
     * @param int $time
     */
    protected function logError(RequestInterface $request, Exception $exception, $time)
    {
        if ($exception instanceof HttpException) {
            $this->logger->error(
                sprintf(
                    "Error:\n%s\nwith response:\n%s\n\nwhen sending request:\n%s",
                    $exception->getMessage(),
                    $this->formatter->formatResponse($exception->getResponse()),
                    $this->formatter->formatRequest($request)
                ),
                [
                    'request' => $request,
                    'response' => $exception->getResponse(),
                    'exception' => $exception,
                    'milliseconds' => $time,
                ]
            );
        } else {
            $this->logger->error(
                sprintf(
                    "Error:\n%s\nwhen sending request:\n%s",
                    $exception->getMessage(),
                    $this->formatter->formatRequest($request)
                ),
                [
                    'request' => $request,
                    'exception' => $exception,
                    'milliseconds' => $time,
                ]
            );
        }
    }
}
