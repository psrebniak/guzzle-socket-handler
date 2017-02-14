<?php

namespace psrebniak\UnixSocketHandler;

/**
 * @return \GuzzleHttp\Client
 */
function getClient() {
    return new \GuzzleHttp\Client([
        'handler' => new \psrebniak\UnixSocketHandler\UnixSocketHandlerFactory(__DIR__ . '/../socat.sock')
    ]);
}