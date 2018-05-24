<?php

namespace Shopify;

use Http\Discovery\UriFactoryDiscovery;
use Shopify\Exception\InvalidArgumentException;

class Utils
{
    /**
     * @param string $domain
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    public static function validateShopDomain(string $domain)
    {
        if (preg_match('/^([a-z0-9\-]{3,100})\.myshopify\.com$/', $domain) !== 1) {
            throw new InvalidArgumentException(
                'Shop name should be 3-100 letters, numbers, or hyphens e.g. your-store.myshopify.com'
            );
        }
    }

    /**
     * @param string $token
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    public static function validateAccessToken(string $token)
    {
        if (preg_match('/^([a-zA-Z0-9]{10,100})$/', $token) !== 1) {
            throw new InvalidArgumentException(
                'Access token should be between 10 and 100 letters and numbers'
            );
        }
    }

    /**
     * @param string $method
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    public static function validateHttpMethod(string $method)
    {
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'GET', 'DELETE', 'HEAD'], true)) {
            throw new InvalidArgumentException('Method not valid');
        }
    }

    /**
     * @param int $length
     * @return string
     * @throws \Exception
     * @see random_bytes()
     */
    public static function randomString(int $length): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * @param string $url
     * @param array|string[] $params
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function removeQueryParams(string $url, array $params): string
    {
        $uri = UriFactoryDiscovery::find()->createUri($url);

        $query = $uri->getQuery();
        if ($query === '') {
            return $uri;
        }

        $decodedKeys = array_map(function ($key) {
            return rawurldecode($key);
        }, $params);

        $result = array_filter(explode('&', $query), function ($part) use ($decodedKeys) {
            return !in_array(rawurldecode(explode('=', $part)[0]), $decodedKeys);
        });

        return $uri->withQuery(implode('&', $result))->__toString();
    }

    /**
     * Verify the request is from Shopify using the HMAC signature (for public apps).
     *
     * @param array $params
     * @param string $secret
     * @return bool
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    public static function verifySignedRequest(array $params, string $secret): bool
    {
        if (empty($secret)) {
            throw InvalidArgumentException('App secret MUST not empty.');
        }

        if (isset($params['hmac']) && isset($params['shop']) && isset($params['timestamp'])) {
            // Get HMAC from params
            $hmac = $params['hmac'];
            unset($params['hmac']);

            ksort($params);

            return $hmac === hash_hmac('sha256', urldecode(http_build_query($params)), $secret);
        }

        return false;
    }
}
