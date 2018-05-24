<?php

namespace Shopify\HttpClient\Message;

use Shopify\Exception\ApiLimitExceedException;
use Psr\Http\Message\ResponseInterface;

class ResponseMediator
{
    /**
     * @param ResponseInterface $response
     * @return array|string
     */
    public static function getContent(ResponseInterface $response)
    {
        $body = $response->getBody()->__toString();
        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $content = json_decode($body, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                return $content;
            }
        }

        return $body;
    }

    /**
     * @param ResponseInterface $response
     * @return array|null
     */
    public static function getPagination(ResponseInterface $response)
    {
        if (!$response->hasHeader('Link')) {
            return;
        }

        $header = self::getHeader($response, 'Link');
        $pagination = [];
        foreach (explode(',', $header) as $link) {
            preg_match('/<(.*)>; rel="(.*)"/i', trim($link, ','), $match);

            if (3 === count($match)) {
                $pagination[$match[2]] = $match[1];
            }
        }

        return $pagination;
    }

    /**
     * @param ResponseInterface $response
     * @return int
     */
    public static function getApiLimit(ResponseInterface $response)
    {
        $apiLimitHeader = self::getHeader($response, 'X-Shopify-Shop-Api-Call-Limit');

        if (null !== $apiLimitHeader && preg_match('#^(\d+)\/(\d+)$#', trim($apiLimitHeader), $matches) === 1) {
            list(, $used, $limit) = $matches;
            if (intval($limit) < intval($used)) {
                throw new ApiLimitExceedException($limit);
            }
        }

        return $limit;
    }

    /**
     * Get the value for a single header.
     *
     * @param ResponseInterface $response
     * @param string $name
     * @return string|null
     */
    public static function getHeader(ResponseInterface $response, $name)
    {
        $headers = $response->getHeader($name);

        return array_shift($headers);
    }
}
