<?php

namespace Shopify\Credential;

use Shopify\Utils;

class AccessToken
{
    /**
     * @var string
     */
    protected $token;

    /**
     * AccessToken constructor.
     *
     * @param string $token
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    public function __construct(string $token)
    {
        Utils::validateAccessToken($token);

        $this->token = $token;
    }

    /**
     * Transform the token to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->token;
    }
}
