<?php

namespace Shopify\Tests;

use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Shopify\Client;

class ClientTest extends TestCase
{
    use NonPublicAccessibleTrait;

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

    /**
     * @test
     */
    public function shouldJsonBodyCreated()
    {
        $params = [
            'a' => 'b',
            'c' => true,
            'd' => null,
        ];
        $client = new Client('your-store.myshopify.com');

        $body = $this->invokeNonPublicMethod($client, 'createJsonBody', $params);

        $this->assertJson($body);
        $this->assertJsonStringEqualsJsonString('{"a":"b", "c": true, "d": null}', $body);
    }
}
