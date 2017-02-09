<?php

require('./vendor/autoload.php');

class SocketException extends \Exception
{
}

class Socket
{
    protected $socket = null;
    protected $path = null;

    protected $domain = null;
    protected $type = null;
    protected $protocol = null;

    /**
     * SocketWrapper constructor.
     * @param int $domain socket_create $domain parameter
     * @param int $type socket_create $type parameter
     * @param int $protocol socket_create $protocol parameter
     *
     * @throws \SocketException
     */
    public function __construct($domain = AF_UNIX, $type = SOCK_STREAM, $protocol = SOL_SOCKET)
    {
        $this->domain = $domain;
        $this->type = $type;
        $this->protocol = $protocol;

        $this->create();
    }

    /**
     * Create new socket if not exist
     * Socket is created in constructor
     *
     * @return $this
     * @throws \SocketException;
     */
    public function create()
    {
        if (!isset($this->socket)) {
            $this->socket = socket_create($this->domain, $this->type, $this->protocol);
            if (!isset($this->socket)) {
                throw new \SocketException("Cannot create socket");
            }
        }

        return $this;
    }

    /**
     * Connect to given socket
     * @param string $path socket path
     *
     * @return $this
     * @throws \SocketException
     */
    public function connect($path)
    {
        if (false === socket_connect($this->socket, $path)) {
            throw new \SocketException("Cannot connect socket to {$path}");
        }
        if (false === socket_set_nonblock($this->socket)) {
            $this->close();
            throw new \SocketException("Cannot set socket as non blocking");
        }

        return $this;
    }

    /**
     * Write to socket
     * @param string $payload
     *
     * @return $this
     * @throws \SocketException
     */
    public function write($payload)
    {
        if (!isset($this->socket) ) {
            throw new \SocketException("Cannot write to empty socket".PHP_EOL);
        }

        if (false === socket_write($this->socket, $payload, strlen($payload))) {
            throw new \SocketException("Error occur when write to stream".PHP_EOL);
        }

        return $this;
    }

    /**
     * Send $payload to stream with flag set
     * @param string $payload
     * @param int $flag socket_send $flag parameter
     *
     * @return $this
     * @throws \SocketException
     */
    public function send($payload, $flag = MSG_EOF)
    {
        if (!isset($this->socket) ) {
            throw new \SocketException("Cannot write to empty socket".PHP_EOL);
        }

        if (false === socket_send($this->socket, $payload, strlen($payload), $flag)) {
            throw new \SocketException("Error occur when send to stream".PHP_EOL);
        }

        return $this;
    }

    /**
     * Close socket
     *
     * @return $this
     */
    public function close()
    {
        if (isset($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }

        return $this;
    }
}

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
        $text = "";

        $socket = $cre


        $line = "{$request->getMethod()} {$request->getRequestTarget()} HTTP/{$request->getProtocolVersion()}" . PHP_EOL;
        $text .= $line;
        socket_write($socket, $line, strlen($line));
        foreach ($request->getHeaders() as $key => $values) {
            $value = implode(', ', $values);
            $line = "{$key}: {$value}" . PHP_EOL;
            $text .= $line;
            socket_write($socket, $line, strlen($line));
        }

        $contentLength = strlen($request->getBody());
//        if ($contentLength > 0) {
//            $line = "Content-length: {$contentLength}" . PHP_EOL;
//            $text .= $line;
//            socket_write($socket, $line, strlen($line));
//        }

        $line = PHP_EOL;
        $text .= $line;
        socket_write($socket, $line, strlen($line));

        $line = $request->getBody();
        $text .= $line;
        socket_write($socket, $line, strlen($line));

        $line = PHP_EOL . PHP_EOL . PHP_EOL;
        $text .= $line;
        socket_send($socket, $line, strlen($line), MSG_EOF);

        echo "---BEGIN---" . PHP_EOL;
        echo $text;
        echo PHP_EOL . "---END---";

        $response = "";
        while (($partial = socket_read($socket, 65384))) {
            $response .= $partial;
        }
        socket_close($socket);

        $responseObject = handle($response);
        return new \GuzzleHttp\Promise\FulfilledPromise($responseObject);
    }

    protected function createSocket()
    {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, SOL_SOCKET);
        if (!$socket) {
            throw new \ErrorException("Cannot create socket");
        }
        if (!socket_connect($socket, $this->path)) {
            throw new \ErrorException("Cannot connect socket to {$this->path}");
        }
        return $socket;
    }

    protected function writeToSocket($socket, $payload)
    {

        return $this
    }

    protected function closeSocket($socket)
    {
        socket_close($socket);
    }
}

;

$guzzle = new GuzzleHttp\Client([
    'handler' => new UnixSocketHandler('/tmp/socket.sock'),
]);

echo $guzzle
    ->post('https://limango.pl/test/123?query&123? hash i zażółć', ['json' => ['a' => 123]])
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


