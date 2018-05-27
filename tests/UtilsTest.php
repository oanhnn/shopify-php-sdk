<?php

namespace Shopify\Tests;

use PHPUnit\Framework\TestCase;
use Shopify\Exception\InvalidArgumentException;
use Shopify\Utils;

class UtilsTest extends TestCase
{
    /**
     * @param string $domain
     * @test
     * @dataProvider provideInvalidShopDomains
     */
    public function shouldThrowExceptionWhenValidateShopDomain(string $domain)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Shop name should be 3-100 letters, numbers, or hyphens e.g. your-store.myshopify.com'
        );

        Utils::validateShopDomain($domain);
    }

    /**
     * @return array
     */
    public function provideInvalidShopDomains()
    {
        return [
            ['-your-store.myshopify.com'],
            ['your-store-.myshopify.com'],
            ['your_store.myshopify.com'],
            ['your-store.myshopify.org'],
            ['your-store.shopify.com'],
            ['your@store.myshopify.com'],
            ['https://your-shop.myshopify.com'],
        ];
    }
}
