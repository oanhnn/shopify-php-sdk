<?php

namespace Shopify\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Shopify\Credential\CredentialInterface;

/**
 * Add authentication to the request.
 */
final class Authentication implements Plugin
{
    /**
     * @var CredentialInterface
     */
    private $credential;

    /**
     * Authentication constructor.
     *
     * @param CredentialInterface $credential
     */
    public function __construct(CredentialInterface $credential)
    {
        $this->credential = $credential;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $next($this->credential->applyToRequest($request));
    }
}
