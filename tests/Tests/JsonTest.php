<?php

namespace psrebniak\GuzzleSocketHandler\Tests;

use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    /** @var  \GuzzleHttp\Client */
    protected $client;

    public function setUp()
    {
        $this->client = \psrebniak\GuzzleSocketHandler\getClient();
    }

    public function testJson()
    {
        $get = [
            'array' => [
                'key' => 'value'
            ]
        ];
        $post = [
            'key1' => 1
        ];

        $request = $this->client->post('/?array[key]=value', [
            'json' => $post
        ]);

        self::assertEquals(200, $request->getStatusCode(), "Post with json return 200");
        $json = \GuzzleHttp\json_decode($request->getBody(), true);

        self::assertEquals("POST", $json['method'], "Requested method match");
        self::assertEquals("application/json", $json['Content-Type'], "Content-Type match");
        self::assertEquals($post, $json['post'], "Post data match");
        self::assertEquals($get, $json['get'], "Get data match");
    }
}