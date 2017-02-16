# Guzzle Socket Handler [![Build Status](https://travis-ci.org/psrebniak/guzzle-socket-handler.svg?branch=master)](https://travis-ci.org/psrebniak/guzzle-socket-handler)  [![Code Climate](https://codeclimate.com/github/psrebniak/guzzle-socket-handler/badges/gpa.svg)](https://codeclimate.com/github/psrebniak/guzzle-socket-handler)

Unix socket handler for guzzle 6. 

## Installation

`composer require psrebniak/guzzle-socket-handler`

## Usage: 
``` 
\GuzzleHttp\Client([
    'handler' => new \psrebniak\GuzzleSocketHandler\SocketHandlerFactory(
        $path
    )
]); 

```

## Request options (from `\psrebniak\GuzzleSocketHandler\SocketOptions`)

* `SOCKET_DOMAIN` - socket_create $domain parameter
* `SOCKET_PROTOCOL` - socket_create $protocol parameter
* `SOCKET_TYPE` - socket_create $type parameter
* `SOCKET_TIMEOUT` - alias of `RequestOptions::CONNECT_TIMEOUT`
* `SOCKET_DEBUG` - alias of `RequestOptions::DEBUG`

## Done:

* sending JSON (`$options[RequestOptions::JSON]` key)
* sending form params (`$options[RequestOptions::FORM_PARAMS]` key)
* sending multipart (`$options[RequestOptions::multipart]` key)
* tracking redirects (`$options[RequestOptions::ALLOW_REDIRECTS]` key)
* timeout (`$options[RequestOptions::CONNECT_TIMEOUT]` key)

## Todo

* 100-Continue Header
* authentication