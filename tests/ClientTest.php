<?php

namespace Shopify\Tests;

use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Shopify\Client;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function shouldNotHaveToPassHttpClientToConstructor()
    {
        $client = new Client('your-store.myshopify.com');

        $this->assertInstanceOf(HttpClient::class, $client->getHttpClient());
    }

    /**
     * @test
     */
    public function shouldPassHttpClientInterfaceToConstructor()
    {
        $httpClientMock = $this->getMockBuilder(HttpClient::class)
            ->getMock();

        $client = Client::createWithHttpClient($httpClientMock, 'your-store.myshopify.com');

        $this->assertInstanceOf(HttpClient::class, $client->getHttpClient());
    }
}
