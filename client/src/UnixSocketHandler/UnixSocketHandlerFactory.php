<?php

namespace UnixSocketHandler;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;

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

        var_dump($options[RequestOptions::ALLOW_REDIRECTS]['max']);

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
                $location = $response->getHeader('Location');
                echo "Redirect to " . array_shift($location) . PHP_EOL;
                $request = new Request('GET', array_shift($location));
            } elseif (in_array($response->getStatusCode(), [307, 308])) {
                $location = $response->getHeader('Location');
                echo "Redirect to " . array_shift($location) . PHP_EOL;
                $request = $request->withRequestTarget(array_shift($location));
            } else {
                break;
            }
        } while ($allowedRedirects >= 0);

        return new FulfilledPromise($response);
    }
}