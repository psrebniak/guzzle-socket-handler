<?php

namespace psrebniak\GuzzleSocketHandler\Tests;


use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    /** @var  \GuzzleHttp\Client */
    protected $client;

    protected $userName = 'userName';
    protected $password = 'passwd';

    public function setUp()
    {
        $this->client = \psrebniak\GuzzleSocketHandler\getClient();
    }

    public function testAuthenticationWithUrl()
    {
        $request = $this->client->post('/?method=authenticate&value=true', [
            \GuzzleHttp\RequestOptions::AUTH => [
                $this->userName,
                $this->password
            ]
        ]);
        $json = \GuzzleHttp\json_decode($request->getBody(), true);

        self::assertEquals([
            'user' => $this->userName,
            'password' => $this->password
        ], $json['authenticate'], "Authentication match");
    }
}