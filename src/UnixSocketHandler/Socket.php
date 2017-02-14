<?php

namespace psrebniak\UnixSocketHandler;

/**
 * Class Socket simple socket wrapper
 * @package UnixSocketHandler
 *
 * @internal
 */
class Socket
{
    /**
     * @var resource|null socket instance
     */
    protected $socket = null;

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
     * @var bool debug flat
     */
    protected $debug = false;

    /**
     * @var string debug mode request string
     */
    protected $debugString = "";

    /**
     * Socket constructor.
     * @param int $domain socket_create $domain parameter
     * @param int $type socket_create $type parameter
     * @param int $protocol socket_create $protocol parameter
     *
     * @throws SocketException
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
     * @throws SocketException;
     */
    public function create()
    {
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }

        if (!isset($this->socket)) {
            $this->socket = @socket_create($this->domain, $this->type, $this->protocol);
            $last_error = socket_last_error($this->socket);
            $error_msg = socket_strerror($last_error);
            if (!isset($this->socket)) {
                throw new SocketException("Cannot create socket. {$error_msg}", $last_error);
            }
        }

        return $this;
    }

    /**
     * Connect to given socket
     * @param string $path socket path
     *
     * @return $this
     * @throws SocketException
     */
    public function connect($path)
    {
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }

        if (false === @socket_connect($this->socket, $path)) {
            $last_error = socket_last_error($this->socket);
            $error_msg = socket_strerror($last_error);
            throw new SocketException("Cannot connect socket to {$path}. {$error_msg}", $last_error);
        }

        return $this;
    }

    /**
     * Write to socket
     * @param string $message
     *
     * @return $this
     * @throws SocketException
     */
    public function write($message)
    {
        if ($this->debug) {
            $this->debugString .= $message;
        }

        if (!isset($this->socket)) {
            throw new SocketException("Cannot write to empty socket.");
        }

        if (false === @socket_write($this->socket, $message, strlen($message))) {
            if ($this->debug) {
                echo "Error occur when write to stream: " . PHP_EOL . "--BEGIN--" . PHP_EOL . $this->debugString . PHP_EOL . "--END--" . PHP_EOL;
            }
            $last_error = socket_last_error($this->socket);
            $error_msg = socket_strerror($last_error);
            throw new SocketException("Error occur when write to stream. {$error_msg}", $last_error);
        }

        return $this;
    }

    /**
     * Send $payload to stream with flag set
     * @param string $message
     * @param int $flag socket_send $flag parameter
     *
     * @return $this
     * @throws SocketException
     */
    public function send($message = "", $flag = MSG_EOR)
    {
        if ($this->debug) {
            $this->debugString .= $message;
            echo "> " . str_replace("\n", "\n> ", $this->debugString) . PHP_EOL . PHP_EOL;
            $this->debugString = "";
        }

        if (!isset($this->socket)) {
            throw new SocketException("Cannot send data to empty socket.");
        }

        if (false === @socket_send($this->socket, $message, strlen($message), $flag)) {
            $last_error = socket_last_error($this->socket);
            $error_msg = socket_strerror($last_error);
            throw new SocketException("Error occur when write to stream. {$error_msg}", $last_error);
        }

        return $this;
    }

    /**
     * Read from socket
     *
     * @param int $type socket_read $type parameter
     * @param int $length socket_read $length parameter
     *
     * @return string
     * @throws SocketException
     */
    public function read($type = PHP_BINARY_READ, $length = 65384)
    {
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }
        if (!isset($this->socket)) {
            throw new SocketException("Cannot read from empty socket");
        }

        return $this->readChunk($length, $type);
    }

    /**
     * @param int $type socket_read $type parameter
     * @param int $chunkLength socket_read $length parameter
     *
     * @return string
     * @throws SocketException
     */
    public function readAll($type = PHP_BINARY_READ, $chunkLength = 65384)
    {
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }
        if (!isset($this->socket)) {
            throw new SocketException("Cannot read from empty socket");
        }

        $response = "";
        while ($partial = $this->readChunk($type, $chunkLength)) {
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
            echo __METHOD__ . PHP_EOL;
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

    /**
     * @param int $type socket_read $type parameter
     * @param int $length socket_read $length parameter
     *
     * @return string
     * @throws SocketException
     */
    protected function readChunk($type, $length)
    {
        $partial = @socket_read($this->socket, $length, $type);
        if (false === $partial) {
            $last_error = socket_last_error($this->socket);
            $error_msg = socket_strerror($last_error);
            throw new SocketException("Error occur when read from stream: {$error_msg}", $last_error);
        }
        return $partial;
    }
}