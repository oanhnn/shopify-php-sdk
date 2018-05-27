<?php

namespace Shopify;

use Http\Client\HttpClient;
use Http\Discovery\UriFactoryDiscovery;
use Psr\Http\Message\UriInterface;
use Shopify\Credential\AccessToken;
use Shopify\Credential\PrivateAppCredential;
use Shopify\Credential\PublicAppCredential;
use Shopify\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopifySDK
{
    /**
     * Version number of the Shopify PHP SDK.
     * @const string
     */
    const VERSION = '1.0.0';

    /**
     * The name of the environment variable that contains the app key.
     *
     * @const string
     */
    const APP_KEY_ENV_NAME = 'SHOPIFY_APP_KEY';

    /**
     * The name of the environment variable that contains the app secret.
     *
     * @const string
     */
    const APP_SECRET_ENV_NAME = 'SHOPIFY_APP_SECRET';

    /**
     * The name of the environment variable that contains the app password.
     *
     * @const string
     */
    const APP_PASSWORD_ENV_NAME = 'SHOPIFY_APP_PASSWORD';

    /**
     * The Shopify app entity.
     *
     * @var string
     */
    protected $appKey;

    /**
     * @var string
     */
    protected $appSecret;

    /**
     * @var string|null
     */
    protected $appPassword;

    /**
     * The Shopify client service.
     *
     * @var Client
     */
    protected $client;

    /**
     * The default access token to use with requests.
     *
     * @var AccessToken|null
     */
    protected $defaultAccessToken;

    /**
     * @var string
     */
    protected $shopDomain;

    /**
     * ShopifySDK constructor.
     *
     * @param array $config
     * @throws InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        $config = $this->prepare($config);

        $this->appKey = $config['app_key'];
        $this->appSecret = $config['app_secret'];
        $this->appPassword = $config['app_password'] ?? null;
        $this->shopDomain = $config['shop_domain'];

        $this->client = $this->createClient($config['http_client']);
        if ($this->appPassword) {
            $this->client->authenticate(
                new PrivateAppCredential($this->appKey, $this->appSecret, $this->appPassword)
            );
        }
    }

    /**
     * @return string
     */
    public function getShopDomain(): string
    {
        return $this->shopDomain;
    }

    /**
     * Returns the Shopify client service.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns the AccessToken entity.
     *
     * @return AccessToken|null
     */
    public function getAccessToken()
    {
        return $this->defaultAccessToken;
    }

    /**
     * Sets the AccessToken entity to use with requests.
     *
     * @param AccessToken|string $accessToken The access token to save.
     * @return void
     * @throws InvalidArgumentException
     */
    public function setAccessToken($accessToken)
    {
        if (!is_string($accessToken) && !$accessToken instanceof AccessToken) {
            throw new InvalidArgumentException(
                'The default access token must be of type "string" or ' . AccessToken::class
            );
        }

        $this->client->authenticate($appCredential = new PublicAppCredential($accessToken));
        $this->defaultAccessToken = $appCredential->getAccessToken();
    }

    /**
     * Generates an authorization URL to begin the process of authenticating a user.
     *
     * @param string $redirectUrl The callback URL to redirect to.
     * @param array|string $scope An array of permissions to request.
     * @param array $params An array of parameters to generate URL.
     * @return string
     */
    public function getAuthorizationUrl(string $redirectUrl, $scope = [], array $params = []): string
    {
        $params += [
            'client_id' => $this->appKey,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => implode(',', is_array($scope) ? $scope : [$scope]),
        ];

        return (string)$this->buildUri('/oauth/authorize', $params);
    }

    /**
     * Get a valid access token from a code.
     *
     * @param string $redirectUri
     * @param string $code
     * @return AccessToken
     * @throws \Http\Client\Exception
     */
    public function getAccessTokenFromCode(string $redirectUri, string $code)
    {
        $params = [
            'client_id' => $this->appKey,
            'client_secret' => $this->appSecret,
            'code' => $code,
            'redirect_uri' => Utils::removeQueryParams($redirectUri, ['state', 'code']),
        ];

        $result = $this->client->post('/oauth/access_token', $params);
        if (!is_array($result) || !isset($result['access_token'])) {
            // TODO: throw exception
        }

        return new AccessToken($result['access_token']);
    }

    /**
     * @param string $path The uri path
     * @param array $params The query parameters
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException
     */
    protected function buildUri(string $path, array $params = []): UriInterface
    {
        return UriFactoryDiscovery::find()->createUri('https://your-store.myshopify.com')
            ->withHost($this->shopDomain)
            ->withPath('/admin/' . ltrim($path, '/'))
            ->withQuery(http_build_query($params, null, '&'));
    }

    /**
     * Prepare config
     *
     * @param array $config
     * @return array
     * @throws InvalidArgumentException
     */
    protected function prepare(array $config): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'app_key' => getenv(static::APP_KEY_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'app_password' => getenv(static::APP_PASSWORD_ENV_NAME),
            'shop_domain' => '',
            'http_client' => null,
        ]);

        $resolver->setAllowedTypes('app_key', 'string');
        $resolver->setAllowedTypes('app_secret', 'string');
        $resolver->setAllowedTypes('app_password', ['null', 'string']);
        $resolver->setAllowedTypes('shop_domain', 'string');
        $resolver->addAllowedTypes('http_client', ['null', HttpClient::class]);

        $option = $resolver->resolve($config);

        if (empty($option['app_key'])) {
            throw new InvalidArgumentException(
                'Required "app_key" key not supplied in config and ' .
                'could not find environment variable "' . static::APP_KEY_ENV_NAME . '"'
            );
        }

        if (empty($option['app_secret'])) {
            throw new InvalidArgumentException(
                'Required "app_secret" key not supplied in config and ' .
                'could not find environment variable "' . static::APP_SECRET_ENV_NAME . '"'
            );
        }

        if (empty($option['shop_domain'])) {
            throw new InvalidArgumentException(
                'Required "shop_domain" key not supplied in config'
            );
        }

        return $option;
    }

    /**
     * @param HttpClient|null $httpClient
     * @return Client
     * @throws InvalidArgumentException
     */
    protected function createClient(HttpClient $httpClient = null): Client
    {
        if ($httpClient) {
            return Client::createWithHttpClient($httpClient, $this->getShopDomain());
        }

        return new Client($this->getShopDomain());
    }
}
