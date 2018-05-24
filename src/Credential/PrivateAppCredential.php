<?php

namespace Shopify\Credential;

use Psr\Http\Message\RequestInterface;

class PrivateAppCredential implements CredentialInterface
{
    /**
     * @var string
     */
    protected $appKey;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var string
     */
    protected $sharedSecret;

    /**
     * PrivateAppCredential constructor.
     * @param string $appKey
     * @param string $sharedSecret
     * @param string $password
     */
    public function __construct(string $appKey, string $sharedSecret, string $password)
    {
        $this->appKey = $appKey;
        $this->password = $password;
        $this->sharedSecret = $sharedSecret;
    }

    /**
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * @param string $appKey
     * @return PrivateAppCredential
     */
    public function setAppKey(string $appKey)
    {
        $this->appKey = $appKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return PrivateAppCredential
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getSharedSecret(): string
    {
        return $this->sharedSecret;
    }

    /**
     * @param string $sharedSecret
     *
     * @return PrivateAppCredential
     */
    public function setSharedSecret(string $sharedSecret)
    {
        $this->sharedSecret = $sharedSecret;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToRequest(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Basic ' . base64_encode("{$this->appKey}:{$this->password}"));
    }
}
