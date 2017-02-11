<?php

namespace UnixSocketHandler;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;

/**
 * Class UnixSocketHandlerFactory
 */
class UnixSocketHandlerFactory
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

    public function __invoke(RequestInterface $request, array $options)
    {
        if (isset($options['delay'])) {
            usleep($options['delay'] * 1000);
        }

        $response = (new UnixSocketHandler(
            $this->path,
            $this->domain,
            $this->type,
            $this->protocol
        ))->handle($request, $options);
        return new FulfilledPromise($response);
    }
}