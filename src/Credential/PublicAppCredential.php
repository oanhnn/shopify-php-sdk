<?php

namespace Shopify\Credential;

use Psr\Http\Message\RequestInterface;

class PublicAppCredential implements CredentialInterface
{
    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * PublicAppCredential constructor.
     *
     * @param AccessToken|string $accessToken
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken instanceof AccessToken ? $accessToken : new AccessToken($accessToken);
    }

    /**
     * Gets the access token.
     *
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToRequest(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('X-Shopify-Access-Token', $this->getAccessToken());
    }
}
