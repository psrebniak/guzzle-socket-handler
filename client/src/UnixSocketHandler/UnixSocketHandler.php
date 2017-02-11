<?php

namespace UnixSocketHandler;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use HttpResponseException;
use Psr\Http\Message\RequestInterface;

/**
 * Class UnixSocketHandler
 *
 * @internal
 */
class UnixSocketHandler
{
    /**
     * https://www.w3.org/Protocols/rfc2616/rfc2616-sec2.html#sec2.2
     */
    const EOF = "\r\n";

    const DEBUG = 'debug';

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

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return \GuzzleHttp\Promise\FulfilledPromise
     */
    public function handle($request, $options)
    {
        $socket = new Socket($this->domain, $this->type, $this->protocol);

        if (isset($options[self::DEBUG]) && $options[self::DEBUG]) {
            $socket->setDebug();
        }
        $socket->connect($this->path);

        $socket->write(
            "{$request->getMethod()} {$request->getRequestTarget()} HTTP/{$request->getProtocolVersion()}" . self::EOF
        );

        $headers = $request->getHeaders();
        // always force connection close header
        $headers['Connection'] = ['close'];

        foreach ($headers as $key => $values) {
            $value = implode(', ', $values);
            $socket->write("{$key}: {$value}" . self::EOF);
        }

        $contentLength = strlen($request->getBody());
        if ($contentLength > 0) {
            $socket->write("Content-Length: {$contentLength}" . self::EOF);
        }

        $socket
            ->write(self::EOF)
            ->write($request->getBody())
            ->write(self::EOF)
            ->send();

        $response = $socket->readAll();
        $socket->close();

        $responseObject = $this->createResponse($response);
        return new FulfilledPromise($responseObject);
    }

    protected function createResponse($data)
    {
        $parts = explode(self::EOF.self::EOF, $data, 2);
        if (count($parts) !== 2) {
            throw new HttpResponseException("Cannot create response from data");
        }
        list($headers, $body) = $parts;
        $headers = explode(self::EOF, $headers);

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

        return new Response(
            $startLine[1],
            $headers,
            $body,
            substr($startLine[0], 5),
            isset($startLine[2]) ? (string)$startLine[2] : null
        );
    }
}