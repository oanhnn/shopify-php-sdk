<?php

namespace Shopify\Credential;

use Psr\Http\Message\RequestInterface;

class PublicAppCredential implements CredentialInterface
{
    /**
     * @var string
     */
    protected $appKey;

    /**
     * @var string
     */
    protected $appSecret;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * PublicAppCredential constructor.
     *
     * @param string $appKey
     * @param string $appSecret
     * @param AccessToken|string $accessToken
     */
    public function __construct(string $appKey, string $appSecret, $accessToken)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
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
