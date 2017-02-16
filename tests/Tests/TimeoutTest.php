<?php

namespace psrebniak\GuzzleSocketHandler\Tests;


use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;
use psrebniak\GuzzleSocketHandler\SocketException;

class TimeoutTest extends TestCase
{
    /** @var  \GuzzleHttp\Client */
    protected $client;

    public function setUp()
    {
        $this->client = \psrebniak\GuzzleSocketHandler\getClient();
    }

    public function testTimeout()
    {
        try {
            $this->client->get('/?sleep=5', [
                RequestOptions::CONNECT_TIMEOUT => 1
            ]);
            self::fail("Request should cause timeout");
        } catch(SocketException $e) {
            self::assertEquals(11, $e->getCode(), "Timed out request got 11 error code");
        }
    }
}