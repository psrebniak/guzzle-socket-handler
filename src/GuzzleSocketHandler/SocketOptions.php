<?php

namespace psrebniak\GuzzleSocketHandler;

use GuzzleHttp\RequestOptions;

class SocketOptions
{
    /**
     * socket_create $domain parameter (int)
     */
    const SOCKET_DOMAIN = 'domain';

    /**
     * socket_create $protocol parameter (int)
     */
    const SOCKET_PROTOCOL = 'protocol';

    /**
     * socket_create $type parameter (int)
     */
    const SOCKET_TYPE = 'type';

    /**
     * Socket connection/read/write timeouts (float)
     */
    const SOCKET_TIMEOUT = RequestOptions::CONNECT_TIMEOUT;

    /**
     * socket debug flag (bool)
     */
    const SOCKET_DEBUG = RequestOptions::DEBUG;
}