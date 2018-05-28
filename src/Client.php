<?php

namespace Shopify;

use Http\Discovery\UriFactoryDiscovery;
use Shopify\Credential\CredentialInterface;
use Shopify\HttpClient\Builder;
use Shopify\HttpClient\Message\ResponseMediator;
use Shopify\HttpClient\Plugin\Authentication;
use Shopify\HttpClient\Plugin\ErrorDetector;
use Shopify\HttpClient\Plugin\History;
use Shopify\HttpClient\Plugin\Retry;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin;
use Http\Client\HttpClient;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Shopify PHP client.
 */
class Client
{
    /**
     * @var Builder
     */
    private $httpClientBuilder;

    /**
     * @var History
     */
    private $history;

    /**
     * @var string Shopify shop domain
     */
    protected $domain;

    /**
     * Instantiate a new Shopify client.
     *
     * @param string $shopDomain
     * @param Builder|null $httpClientBuilder
     */
    public function __construct(string $shopDomain, Builder $httpClientBuilder = null)
    {
        $this->history = new History();
        $this->httpClientBuilder = $builder = $httpClientBuilder ?: new Builder();

        $builder->addPlugin(new ErrorDetector());
        $builder->addPlugin(new Plugin\HistoryPlugin($this->history));
        $builder->addPlugin(new Retry(['retries' => 1]));
        $builder->addPlugin(new Plugin\RedirectPlugin());
        $builder->addPlugin(new Plugin\HeaderDefaultsPlugin([
            'User-Agent' => 'Shopify-php-sdk (https://github.com/oanhnn/shopify-php-sdk)',
        ]));

        $builder->addHeaderValue('Accept', 'application/json');

        $this->setShopDomain($shopDomain);
    }

    /**
     * Create a Shopify\Client using a HttpClient.
     *
     * @param HttpClient $httpClient
     * @param string $shopDomain
     * @return Client
     */
    public static function createWithHttpClient(HttpClient $httpClient, string $shopDomain)
    {
        $builder = new Builder($httpClient);

        return new self($shopDomain, $builder);
    }

    /**
     * Send a GET request with query parameters.
     *
     * @param string $path    Request path.
     * @param array  $params  GET parameters.
     * @param array  $headers Request Headers.
     * @return array|string
     * @throws \Http\Client\Exception
     */
    public function get($path, array $params = [], array $headers = [])
    {
        $uri = $this->buildUri($path, $params);
        $response = $this->getHttpClient()->get($uri, $headers);

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a HEAD request with query parameters.
     *
     * @param string $path    Request path.
     * @param array  $params  HEAD parameters.
     * @param array  $headers Request headers.
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Http\Client\Exception
     */
    public function head($path, array $params = [], array $headers = [])
    {
        $uri = $this->buildUri($path, $params);

        return $this->getHttpClient()->head($uri, $headers);
    }

    /**
     * Send a POST request with JSON-encoded parameters.
     *
     * @param string $path    Request path.
     * @param array  $params  POST parameters to be JSON encoded.
     * @param array  $headers Request headers.
     * @return array|string
     * @throws \Http\Client\Exception
     */
    public function post($path, array $params = [], array $headers = [])
    {
        return $this->postRaw(
            $path,
            $this->createJsonBody($params),
            array_merge(['Content-Type' => ['application/json']], $headers)
        );
    }

    /**
     * Send a POST request with raw data.
     *
     * @param string $path           Request path.
     * @param string $body           Request body.
     * @param array  $headers Request headers.
     * @return array|string
     * @throws \Http\Client\Exception
     */
    public function postRaw($path, $body, array $headers = [])
    {
        $uri = $this->buildUri($path);
        $response = $this->getHttpClient()->post(
            $uri,
            $headers,
            $body
        );

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a PATCH request with JSON-encoded parameters.
     *
     * @param string $path    Request path.
     * @param array  $params  POST parameters to be JSON encoded.
     * @param array  $headers Request headers.
     * @return array|string
     * @throws \Http\Client\Exception
     */
    public function patch($path, array $params = [], array $headers = [])
    {
        $uri = $this->buildUri($path);
        $response = $this->getHttpClient()->patch(
            $uri,
            array_merge(['Content-Type' => ['application/json']], $headers),
            $this->createJsonBody($params)
        );

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a PUT request with JSON-encoded parameters.
     *
     * @param string $path    Request path.
     * @param array  $params  POST parameters to be JSON encoded.
     * @param array  $headers Request headers.
     * @return array|string
     * @throws \Http\Client\Exception
     */
    public function put($path, array $params = [], array $headers = [])
    {
        $uri = $this->buildUri($path);
        $response = $this->getHttpClient()->put(
            $uri,
            array_merge(['Content-Type' => ['application/json']], $headers),
            $this->createJsonBody($params)
        );

        return ResponseMediator::getContent($response);
    }

    /**
     * Send a DELETE request with JSON-encoded parameters.
     *
     * @param string $path           Request path.
     * @param array  $params     POST parameters to be JSON encoded.
     * @param array  $headers Request headers.
     * @return array|string
     * @throws \Http\Client\Exception
     */
    public function delete($path, array $params = [], array $headers = [])
    {
        $uri = $this->buildUri($path, $params);
        $response = $this->getHttpClient()->delete(
            $uri,
            array_merge(['Content-Type' => ['application/json']], $headers),
            $this->createJsonBody($params)
        );

        return ResponseMediator::getContent($response);
    }

    /**
     * @param string $path The uri path
     * @param array $params The query parameters
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    public function buildUri(string $path, array $params = []): string
    {
        return UriFactoryDiscovery::find()
            ->createUri('https://your-store.myshopify.com')
            ->withHost($this->domain)
            ->withPath('/admin/' . ltrim($path, '/'))
            ->withQuery(http_build_query($params, null, '&'))
            ->__toString();
    }

    /**
     * Authenticate a user for all next requests.
     *
     * @param CredentialInterface $credential
     * @return Client
     */
    public function authenticate(CredentialInterface $credential)
    {
        $this->getHttpClientBuilder()->removePlugin(Authentication::class);
        $this->getHttpClientBuilder()->addPlugin(new Authentication($credential));

        return $this;
    }

    /**
     * Add a cache plugin to cache responses locally.
     *
     * @param CacheItemPoolInterface $cachePool
     * @return Client
     * @param array $config
     */
    public function addCache(CacheItemPoolInterface $cachePool, array $config = [])
    {
        $this->getHttpClientBuilder()->addCache($cachePool, $config);

        return $this;
    }

    /**
     * Remove the cache plugin.
     * @return Client
     */
    public function removeCache()
    {
        $this->getHttpClientBuilder()->removeCache();

        return $this;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getLastResponse()
    {
        return $this->history->getLastResponse();
    }

    /**
     * @return \Psr\Http\Message\RequestInterface|null
     */
    public function getLastRequest()
    {
        return $this->history->getLastRequest();
    }

    /**
     * @return HttpMethodsClient
     */
    public function getHttpClient()
    {
        return $this->getHttpClientBuilder()->getHttpClient();
    }

    /**
     * @return Builder
     */
    protected function getHttpClientBuilder()
    {
        return $this->httpClientBuilder;
    }

    /**
     * Create a JSON encoded version of an array of parameters.
     *
     * @param array $params Request parameters
     * @return null|string
     */
    protected function createJsonBody(array $params)
    {
        return (count($params) === 0) ? null : json_encode($params, empty($params) ? JSON_FORCE_OBJECT : 0);
    }

    /**
     * Sets the Shopify shop domain
     *
     * @param string $domain The Shopify shop domain e.g. your-store.myshopify.com
     * @return Client
     * @throws \Shopify\Exception\InvalidArgumentException
     */
    protected function setShopDomain(string $domain)
    {
        Utils::validateShopDomain($domain);
        $this->domain = $domain;

        return $this;
    }
}
