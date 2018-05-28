<?php

namespace Shopify\HttpClient\Plugin;

use Http\Client\Common\Plugin\Journal;
use Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * A plugin to remember the last response.
 */
final class History implements Journal
{
    /**
     * @var RequestInterface
     */
    private $lastRequest;

    /**
     * @var ResponseInterface
     */
    private $lastResponse;

    /**
     * @return RequestInterface|null
     */
    public function getLastRequest(): ? RequestInterface
    {
        return $this->lastRequest;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getLastResponse(): ? ResponseInterface
    {
        return $this->lastResponse;
    }

    /**
     * Log success
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function addSuccess(RequestInterface $request, ResponseInterface $response)
    {
        $this->lastRequest = $request;
        $this->lastResponse = $response;
    }

    /**
     * Log error
     *
     * @param RequestInterface $request
     * @param Exception $exception
     */
    public function addFailure(RequestInterface $request, Exception $exception)
    {
        // No do thing
    }
}
