<?php

namespace psrebniak\GuzzleSocketHandler\Tests;

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /** @var  \GuzzleHttp\Client */
    protected $client;

    public function setUp()
    {
        $this->client = \psrebniak\GuzzleSocketHandler\getClient();
    }

    public function testConnection()
    {
        self::assertEquals(200, $this->client->get('/')->getStatusCode(), "Server is responding");
    }
}
