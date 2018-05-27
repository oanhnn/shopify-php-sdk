<?php

namespace Shopify;

use Shopify\Credential\AccessToken;
use Shopify\Credential\PrivateAppCredential;
use Shopify\Credential\PublicAppCredential;
use Shopify\Exception\InvalidArgumentException;

class App
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $password;

    /**
     * App constructor.
     * @param string $key
     * @param string $secret
     * @param string|null $password
     */
    public function __construct(string $key, string $secret, string $password = null)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return !is_null($this->password);
    }

    /**
     * @return PrivateAppCredential
     */
    public function makePrivateCredential(): PrivateAppCredential
    {
        return new PrivateAppCredential($this->key, $this->secret, $this->password);
    }

    /**
     * @param AccessToken|string $accessToken
     * @return PublicAppCredential
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    public function makePublicCredential($accessToken): PublicAppCredential
    {
        if (!is_string($accessToken) && !$accessToken instanceof AccessToken) {
            throw new InvalidArgumentException(
                'The default access token must be of type "string" or ' . AccessToken::class
            );
        }

        return new PublicAppCredential($this->key, $this->secret, $accessToken);
    }
}
