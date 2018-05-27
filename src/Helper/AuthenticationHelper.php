<?php

namespace Shopify\Helper;

use Shopify\App;
use Shopify\Client;
use Shopify\Credential\AccessToken;

class AuthenticationHelper
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Client
     */
    protected $client;

    /**
     * AuthenticationHelper constructor.
     * @param App $app
     * @param Client $client
     */
    public function __construct(App $app, Client $client)
    {
        $this->app = $app;
        $this->client = $client;
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
            'client_id' => $this->app->getKey(),
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => implode(',', is_array($scope) ? $scope : [$scope]),
        ];

        return $this->client->buildUri('/oauth/authorize', $params);
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
            'client_id' => $this->app->getKey(),
            'client_secret' => $this->app->getSecret(),
            'code' => $code,
            'redirect_uri' => Utils::removeQueryParams($redirectUri, ['state', 'code']),
        ];

        $result = $this->client->post('/oauth/access_token', $params);
        if (!is_array($result) || !isset($result['access_token'])) {
            // TODO: throw exception
        }

        return new AccessToken($result['access_token']);
    }
}
