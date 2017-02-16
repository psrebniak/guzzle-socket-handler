# Guzzle Socket Handler [![Build Status](https://travis-ci.org/psrebniak/guzzle-socket-handler.svg?branch=master)](https://travis-ci.org/psrebniak/guzzle-socket-handler)  [![Code Climate](https://codeclimate.com/github/psrebniak/guzzle-socket-handler/badges/gpa.svg)](https://codeclimate.com/github/psrebniak/guzzle-socket-handler)

Unix socket handler for guzzle 6. 

## Installation

`composer require psrebniak/guzzle-socket-handler`

## Usage: 
``` 
\GuzzleHttp\Client([
    'handler' => new \psrebniak\GuzzleSocketHandler\SocketHandlerFactory(
        // path to unix socket
        $path, 
        // socket_create parameters
        $domain /* = AF_UNIX */, 
        $type /* = SOCK_STREAM */,
        $protocol /* = SOL_SOCKET */
    )
]);
```

## Done:

* sending JSON (`$options[RequestOptions::JSON]` key)
* sending form params (`$options[RequestOptions::FORM_PARAMS]` key)
* sending multipart (`$options[RequestOptions::multipart]` key)
* tracking redirects (`$options[RequestOptions::ALLOW_REDIRECTS]` key)
* timeout (`$options[RequestOptions::CONNECT_TIMEOUT]` key)

## Todo

* 100-Continue Header
* authentication