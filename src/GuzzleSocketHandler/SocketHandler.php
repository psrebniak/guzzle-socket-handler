<?php

namespace psrebniak\GuzzleSocketHandler;

use GuzzleHttp\Psr7\Response;
use HttpResponseException;
use Psr\Http\Message\RequestInterface;

/**
 * Class GuzzleSocketHandler
 *
 * @internal
 */
class SocketHandler
{
    /**
     * https://www.w3.org/Protocols/rfc2616/rfc2616-sec2.html#sec2.2
     */
    const EOL = "\r\n";

    /**
     * guzzle debug flag
     */
    const DEBUG = 'debug';

    /**
     * @var string $path socket path
     */
    protected $path;

    /**
     * @var int $domain socket_create $domain parameter
     */
    protected $domain;

    /**
     * @var int socket_create $type parameter
     */
    protected $type;

    /**
     * @var int socket_create $protocol parameter
     */
    protected $protocol;

    /**
     * @var null|Socket
     */
    protected $socket = null;

    /**
     * @param string $path valid socket path with unix:// protocol
     * @param int $domain socket_create $domain parameter
     * @param int $type socket_create
     * @param int $protocol
     */
    public function __construct($path, $domain = AF_UNIX, $type = SOCK_STREAM, $protocol = SOL_SOCKET)
    {
        $this->path = $path;
        $this->domain = $domain;
        $this->type = $type;
        $this->protocol = $protocol;
    }

    public function __destruct()
    {
        if (isset($this->socket)) {
            $this->socket->close();
        }
    }

    /**
     * Handle connection
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return Response
     * @throws HttpResponseException
     * @throws SocketException
     */
    public function handle($request, $options)
    {
        $socket = $this->getSocket();

        if (isset($options[self::DEBUG]) && $options[self::DEBUG]) {
            $socket->setDebug();
        }
        $socket->connect($this->path);

        $socket->write(sprintf(
            "%s %s HTTP/%s" . self::EOL,
            strtoupper($request->getMethod()),
            $request->getRequestTarget(),
            $request->getProtocolVersion()
        ));

        $headers = $request->getHeaders();
        $body = $request->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        // set content-length if not set
        if (!$request->hasHeader('Content-Length') &&
            $body->getSize() > 0
        ) {
            $headers['Content-Length'] = [$body->getSize()];
        }

        foreach ($headers as $key => $values) {
            $value = implode(', ', $values);
            $socket->write("{$key}: {$value}" . self::EOL);
        }

        $socket
            ->write(self::EOL)
            ->write($body->getContents())
            ->write(self::EOL)
            ->send();

        $response = $socket->readAll();
        $socket->close();

        return $this->createResponse($response);
    }

    /**
     * @param $data
     * @return Response
     * @throws \HttpResponseException
     */
    protected function createResponse($data)
    {
        $parts = explode(self::EOL . self::EOL, $data, 2);
        if (count($parts) !== 2) {
            throw new \HttpResponseException("Cannot create response from data");
        }
        list($headers, $body) = $parts;
        $headers = explode(self::EOL, $headers);

        /// guzzle EasyHandle copy

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

                unset($headers[$normalizedKeys['content-length']]);
                $bodyLength = (int)strlen($body);
                if ($bodyLength) {
                    $headers[$normalizedKeys['content-length']] = $bodyLength;
                }
            }
        }

        return new Response(
            $startLine[1],
            $headers,
            $body,
            substr($startLine[0], 5),
            isset($startLine[2]) ? (string)$startLine[2] : null
        );
    }

    protected function getSocket()
    {
        if (!isset($this->socket)) {
            $this->socket = new Socket($this->domain, $this->type, $this->protocol);
        }
        $this->socket->create();

        return $this->socket;
    }
}