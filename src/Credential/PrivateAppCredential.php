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
    protected $sharedSecret;

    /**
     * @var string
     */
    protected $password;

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
     * {@inheritdoc}
     */
    public function applyToRequest(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Basic ' . base64_encode("{$this->appKey}:{$this->password}"));
    }
}
