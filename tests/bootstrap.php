<?php

namespace UnixSocketHandler;

/**
 * @return \GuzzleHttp\Client
 */
function getClient() {
    return new \GuzzleHttp\Client([
        'handler' => new \UnixSocketHandler\UnixSocketHandlerFactory(__DIR__ . '/../socat.sock')
    ]);
}