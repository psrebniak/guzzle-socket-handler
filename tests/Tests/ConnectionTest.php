<?php

namespace UnixSocketHandler\Tests;

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    /** @var  \GuzzleHttp\Client */
    protected $client;

    public function setUp()
    {
        $this->client = \UnixSocketHandler\getClient();
    }

    public function testConnection() {
        self::assertEquals(200, $this->client->get('/')->getStatusCode(), "Server is responding");
    }
}
