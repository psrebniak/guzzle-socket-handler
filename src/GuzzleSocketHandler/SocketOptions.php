<?php

namespace psrebniak\GuzzleSocketHandler;

use GuzzleHttp\RequestOptions;

class SocketOptions
{
    /**
     * Socket connection/read/write timeouts (float)
     */
    const SOCKET_TIMEOUT = RequestOptions::CONNECT_TIMEOUT;

    /**
     * socket debug flag (bool)
     */
    const SOCKET_DEBUG = RequestOptions::DEBUG;
}