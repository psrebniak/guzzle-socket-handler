<?php

use UnixSocketHandler\UnixSocketHandlerFactory;

$guzzle = new GuzzleHttp\Client([
    'handler' => new UnixSocketHandlerFactory('/tmp/socket.sock'),
]);

echo $guzzle
    ->post('https://limango.pl/test/123?query&123? hash i zażółć', [
        'json' => ['a' => 123],
        'headers' => [
//            'Accept'     => 'application/json',
        ]
    ])
    ->getBody()->getContents();


