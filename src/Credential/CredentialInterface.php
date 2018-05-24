<?php

namespace Shopify\Credential;

use Psr\Http\Message\RequestInterface;

interface CredentialInterface
{
    /**
     * Apply the credential to the request.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function applyToRequest(RequestInterface $request): RequestInterface;
}
