<?php

namespace Shopify;

use Http\Client\HttpClient;
use Shopify\Credential\AccessToken;
use Shopify\Exception\InvalidArgumentException;
use Shopify\Helper\AuthenticationHelper;
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
     * @var App
     */
    protected $app;

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
     * Shopify shop domain
     *
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

        $this->app = new App($config['app_key'], $config['app_secret'], $config['app_password']);
        $this->shopDomain = $config['shop_domain'];
        $this->client = $this->createClient($config['http_client']);

        if ($this->app->isPrivate()) {
            $this->client->authenticate($this->app->makePrivateCredential());
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
     * @return ShopifySDK
     * @throws InvalidArgumentException
     */
    public function setAccessToken($accessToken)
    {
        $appCredential = $this->app->makePublicCredential($accessToken);

        $this->defaultAccessToken = $appCredential->getAccessToken();
        $this->client->authenticate($appCredential);

        return $this;
    }

    /**
     * @return AuthenticationHelper
     */
    public function getAuthenticationHelper()
    {
        return new AuthenticationHelper($this->app, $this->client);
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
