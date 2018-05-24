# Shopify PHP SDK

[![Build Status](https://travis-ci.org/oanhnn/shopify-php-sdk.svg?branch=master)](https://travis-ci.org/oanhnn/shopify-php-sdk)
[![Coverage Status](https://coveralls.io/repos/github/oanhnn/shopify-php-sdk/badge.svg?branch=master)](https://coveralls.io/github/oanhnn/shopify-php-sdk?branch=master)

Unoffical Shopify SDK for PHP

## Requirements

* php >=7.1.3

## Installation

Begin by pulling in the package through Composer.

```bash
$ composer require oanhnn/shopify-php-sdk php-http/guzzle6-adapter
```

Why `php-http/guzzle6-adapter`? We are decoupled from any HTTP messaging client with help by HTTPlug. 
You can find other HTTP messaging client implement `\Http\Client\HttpClient` in [here](https://packagist.org/providers/php-http/client-implementation).

## Usage

```php
$sdk = new ShopifySDK([
    'app_key' => getenv(static::APP_KEY_ENV_NAME),
    'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
    'app_password' => getenv(static::APP_PASSWORD_ENV_NAME),
    'shop_domain' => 'your-store.myshopify.com',
    'http_client' => new \Http\Adapter\Guzzle6\Client(),
]);

// Make authorization url
$sdk->getAuthorizationUrl(
    'https://example.com/shopify',
    ['read_products', 'write_products'],
    ['state' => 'random-string']
);

// Get access token from code
$accessToken = $sdk->getAccessTokenFromCode(
    'https://example.com/shopify',
    $_GET['code']
);

// Get shop information
$shop = $sdk->setAccessToken($accessToken)->getClient()->get('/shop.json');

```

## Changelog

See all change logs in [CHANGELOG](CHANGELOG.md)

## Testing

```bash
$ git clone git@github.com/oanhnn/shopify-php-sdk.git /path
$ cd /path
$ composer install
$ composer phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email to [Oanh Nguyen](mailto:oanhnn.bk@gmail.com) instead of 
using the issue tracker.

## Credits

- [Oanh Nguyen](https://github.com/oanhnn)
- [All Contributors](../../contributors)

## License

This project is released under the MIT License.   
Copyright © 2018 [Oanh Nguyen](https://oanhnn.github.io/).
