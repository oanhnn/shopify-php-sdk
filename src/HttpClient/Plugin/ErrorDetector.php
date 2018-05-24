<?php

namespace Shopify\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shopify\Exception\ApiLimitExceedException;
use Shopify\Exception\ErrorException;
use Shopify\Exception\RuntimeException;
use Shopify\Exception\ValidationFailedException;
use Shopify\HttpClient\Message\ResponseMediator;

/**
 * Throw exception when the response of a request is not acceptable.
 *
 * Status codes 400-499 lead to a ClientErrorException, status 500-599 to a ServerErrorException.
 */
final class ErrorDetector implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $next($request)->then(function (ResponseInterface $response) use ($request) {
            return $this->transformResponseToException($request, $response);
        });
    }

    /**
     * Transform response to an error if possible.
     *
     * @param RequestInterface $request Request of the call
     * @param ResponseInterface $response Response of the call
     * @return ResponseInterface If status code is not in 4xx or 5xx return response
     */
    protected function transformResponseToException(RequestInterface $request, ResponseInterface $response)
    {
        // TODO

        return $response;
    }
}
