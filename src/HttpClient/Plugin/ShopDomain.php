<?php

namespace Shopify\HttpClient\Plugin;

use Http\Client\Common\Plugin;
use Http\Discovery\UriFactoryDiscovery;
use Psr\Http\Message\RequestInterface;

final class ShopDomain implements Plugin
{
    /**
     * @var \Psr\Http\Message\UriInterface
     */
    private $host;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param string $domain The Shopify shop domain e.g. your-store.myshopify.com
     * @param bool $forceReplace
     */
    public function __construct(string $domain, bool $forceReplace = false)
    {
        $this->host = UriFactoryDiscovery::find()->createUri('https://your-store.myshopify.com')->withHost($domain);
        $this->force = $forceReplace;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        if ($this->force || $request->getUri()->getHost() === '') {
            $uri = $request->getUri()
                ->withHost($this->host->getHost())
                ->withScheme($this->host->getScheme())
                ->withPort($this->host->getPort());

            $request = $request->withUri($uri);
        }

        return $next($request);
    }
}
