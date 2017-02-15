# Guzzle Socket Handler

Unix socket handler for guzzle 6. 

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

## Todo

* 100-Continue Header
* authentication
* tests