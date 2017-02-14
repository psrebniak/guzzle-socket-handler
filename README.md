# Unix Socket Handler

Unix socket handler for guzzle 6. 

## Usage: 
``` 
\GuzzleHttp\Client([
    'handler' => new \psrebniak\UnixSocketHandler\UnixSocketHandlerFactory(
        $path,
        $domain /* = AF_UNIX */, 
        $type /* = SOCK_STREAM */, 
        $protocol /* = SOL_SOCKET */
    )
]);
```

## Whats work:

* sending JSON (`$options[RequestOptions::JSON]` key)
* sending form params (`$options[RequestOptions::FORM_PARAMS]` key)
* sending multipart (`$options[RequestOptions::multipart]` key)
* tracking redirects (`$options[RequestOptions::ALLOW_REDIRECTS]` key)

## What's not work

* 100-Continue Header
* authentication