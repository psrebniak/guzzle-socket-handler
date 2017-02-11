<?php

use UnixSocketHandler\Socket;

class UnixSocketHandler
{
    protected $path;
    protected $domain;
    protected $type;
    protected $protocol;

    /**
     * @param string $path valid socket path with unix:// protocol
     * @param int $domain socket_create $domain parameter
     * @param int $type socket_create
     * @param int $protocol
     */
    public function __construct(string $path, $domain = AF_UNIX, $type = SOCK_STREAM, $protocol = SOL_SOCKET)
    {
        $this->path = $path;
        $this->domain = $domain;
        $this->type = $type;
        $this->protocol = $protocol;
    }

    public function __invoke(\Psr\Http\Message\RequestInterface $request, array $options)
    {
        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }
        $socket = new Socket($this->domain, $this->type, $this->protocol);
        $socket->setDebug();
        $socket->connect($this->path);
        $socket->write("{$request->getMethod()} {$request->getRequestTarget()} HTTP/{$request->getProtocolVersion()}" . Socket::EOL);

        foreach ($request->getHeaders() as $key => $values) {
            $value = implode(', ', $values);
            $socket->write("{$key}: {$value}" . Socket::EOL);
        }

        $contentLength = strlen($request->getBody());
//        if ($contentLength > 0) {
//            $socket->write("Content-Length: {$contentLength}" . Socket::EOL);
//        }

        $socket
            ->write(Socket::EOL)
            ->write($request->getBody().Socket::EOL)
            ->send();

        $response = $socket->read();
        $socket->close();

        var_dump($response);

        $responseObject = handle($response);
        return new \GuzzleHttp\Promise\FulfilledPromise($responseObject);
    }

}


$guzzle = new GuzzleHttp\Client([
    'handler' => new UnixSocketHandler('/tmp/socket.sock'),
]);

echo $guzzle
    ->post('https://limango.pl/test/123?query&123? hash i zażółć', [
        'json' => ['a' => 123],
        'headers' => [
            'Accept'     => 'application/json',
        ]
    ])
    ->getBody()->getContents();

die;

$headers = <<<EOF
GET / HTTP/1.0
HOST: example.com
User-Agent: ncat/unknown
Accept: */*


EOF;

function handle($response)
{
    list($headers, $body) = preg_split('/(\r?\n){2}/', $response, 2);
    $headers = explode(PHP_EOL, $headers);

    $startLine = explode(' ', array_shift($headers), 3);
    $headers = \GuzzleHttp\headers_from_lines($headers);
    $normalizedKeys = \GuzzleHttp\normalize_header_keys($headers);

    if (isset($normalizedKeys['content-encoding'])) {
        $headers['x-encoded-content-encoding']
            = $headers[$normalizedKeys['content-encoding']];
        unset($headers[$normalizedKeys['content-encoding']]);
        if (isset($normalizedKeys['content-length'])) {
            $headers['x-encoded-content-length']
                = $headers[$normalizedKeys['content-length']];

            $bodyLength = (int)strlen($body);
            if ($bodyLength) {
                $headers[$normalizedKeys['content-length']] = $bodyLength;
            } else {
                unset($headers[$normalizedKeys['content-length']]);
            }
        }
    }

    // Attach a response to the easy handle with the parsed headers.
    return new \GuzzleHttp\Psr7\Response(
        $startLine[1],
        $headers,
        $body,
        substr($startLine[0], 5),
        isset($startLine[2]) ? (string)$startLine[2] : null
    );
}


