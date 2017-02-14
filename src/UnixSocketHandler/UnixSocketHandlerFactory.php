<?php

namespace psrebniak\UnixSocketHandler;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class UnixSocketHandlerFactory
 */
class UnixSocketHandlerFactory
{
    /**
     * @var string socket path
     */
    protected $path;

    /**
     * @var int socket_create $domain parameter
     */
    protected $domain;

    /**
     * @var int socket_create $type parameter
     */
    protected $type;

    /**
     * @var int socket_create $type parameter
     */
    protected $protocol;

    /**
     * @param string $path valid socket path
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

    public function __invoke(RequestInterface $request, array $options)
    {
        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }
        // set full uri request target with all keys (protocol, host etc)
        $request = $request->withRequestTarget((string)$request->getUri());
        $socket = new UnixSocketHandler(
            $this->path,
            $this->domain,
            $this->type,
            $this->protocol
        );
        $allowedRedirects = 0;
        if (isset($options[RequestOptions::ALLOW_REDIRECTS]['max'])) {
            $allowedRedirects = $options[RequestOptions::ALLOW_REDIRECTS]['max'];
        }

        do {
            $allowedRedirects--;
            $response = $socket->handle($request, $options);
            if (in_array($response->getStatusCode(), [301, 302, 303])) {
                $request = $this->createRedirect($request, $response, 'GET', $options);
            } elseif (in_array($response->getStatusCode(), [307, 308])) {
                $request = $this->createRedirect($request, $response, $request->getMethod(), $options);
            } else {
                break;
            }
        } while ($allowedRedirects >= 0);

        return new FulfilledPromise($response);
    }

    protected function createRedirect(RequestInterface $request, ResponseInterface $response, $method, $options)
    {
        if ($options[RequestOptions::ALLOW_REDIRECTS]['referer']) {
            $request->withHeader('referer', $request->getRequestTarget());
        }
        if ($options[RequestOptions::ALLOW_REDIRECTS]['track_redirects']) {
            $request = $request
                ->withAddedHeader('X-Guzzle-Redirect-History', $request->getRequestTarget())
                ->withAddedHeader('X-Guzzle-Redirect-Status-History', $response->getStatusCode());
        }

        $location = $response->getHeader('Location');
        return $request->withMethod($method)->withRequestTarget(array_shift($location));
    }
}