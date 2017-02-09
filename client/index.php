<?php

require('./vendor/autoload.php');

class SocketException extends \Exception
{
}

class Socket
{
    const EOL = "\r\n";

    protected $socket = null;
    protected $path = null;

    protected $domain = null;
    protected $type = null;
    protected $protocol = null;

    protected $debug = false;

    /**
     * Socket constructor.
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
     * Socket destructor, call close method
     */
    public function __destruct()
    {
        $this->close();
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
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }

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
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }

        if (false === socket_connect($this->socket, $path)) {
            throw new \SocketException("Cannot connect socket to {$path}");
        }
//        if (false === socket_set_nonblock($this->socket)) {
//            $this->close();
//            throw new \SocketException("Cannot set socket as non blocking");
//        }

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
        if ($this->debug) {
            echo "> " . $payload;
        }

        if (!isset($this->socket)) {
            throw new \SocketException("Cannot write to empty socket" . PHP_EOL);
        }

        if (false === socket_write($this->socket, $payload, strlen($payload))) {
            throw new \SocketException("Error occur when write to stream" . PHP_EOL);
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
    public function send($payload, $flag = MSG_EOR)
    {
        if ($this->debug) {
            echo "> " . $payload;
        }
        if (!isset($this->socket)) {
            throw new \SocketException("Cannot write to empty socket" . PHP_EOL);
        }

        if (false === socket_send($this->socket, $payload, strlen($payload), $flag)) {
            throw new \SocketException("Error occur when send to stream" . PHP_EOL);
        }

        return $this;
    }

    /**
     * Read from socket
     *
     * @param int $type socket_read $type parameter
     *
     * @return string
     * @throws SocketException
     */
    public function read($type = PHP_BINARY_READ)
    {
        if ($this->debug) {
            echo __METHOD__.PHP_EOL;
        }
        if (!isset($this->socket)) {
            throw new \SocketException("Cannot read from empty socket" . PHP_EOL);
        }

        $response = "";
        while (($partial = socket_read($this->socket, 65384, $type))) {
            if (false === $partial) {
                $last_error = socket_last_error($this->socket);
                $message = socket_strerror($last_error);
                throw new SocketException("Error occur when read from stream: {$message}", $last_error);
            }
            $response .= $partial;
        }

        return $response;
    }

    /**
     * Close socket
     *
     * @return $this
     */
    public function close()
    {
        if ($this->debug) {
            echo __METHOD__.PHP_EOL;
        }

        if (isset($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }

        return $this;
    }

    /**
     * Set debugging flag
     * Debugger echoes methods calls
     *
     * @param bool $debug
     */
    public function setDebug($debug = true)
    {
        $this->debug = (bool)$debug;
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
        $socket = new Socket($this->domain, $this->type, $this->protocol);
        $socket->setDebug();
        $socket->connect($this->path);
        $socket->write("{$request->getMethod()} {$request->getRequestTarget()} HTTP/{$request->getProtocolVersion()}" . Socket::EOL);

        foreach ($request->getHeaders() as $key => $values) {
            $value = implode(', ', $values);
            $socket->write("{$key}: {$value}" . Socket::EOL);
        }

        $contentLength = strlen($request->getBody());
        if ($contentLength > 0) {
            $socket->write("Content-Length: {$contentLength}" . Socket::EOL);
        }

        $socket
            ->write(Socket::EOL)
            ->write($request->getBody())
            ->send(Socket::EOL);

        var_dump($socket->read());
        $socket->close();
        die;

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


