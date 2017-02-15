<?php

namespace psrebniak\GuzzleSocketHandler;

/**
 * @return \GuzzleHttp\Client
 */
function getClient()
{
    return new \GuzzleHttp\Client([
        'handler' => new \psrebniak\GuzzleSocketHandler\SocketHandlerFactory(__DIR__ . '/../socat.sock')
    ]);
}