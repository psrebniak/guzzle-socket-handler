<?php

namespace psrebniak\GuzzleSocketHandler;

/**
 * Class Socket simple socket wrapper
 * @package GuzzleSocketHandler
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
    protected $path = null;

    /**
     * @var int socket_create $domain parameter
     */
    protected $domain = AF_UNIX;

    /**
     * @var int socket_create $type parameter
     */
    protected $type = SOCK_STREAM;

    /**
     * @var int socket_create $type parameter
     */
    protected $protocol = SOL_SOCKET;

    /**
     * @var bool debug flat
     */
    protected $debug = false;

    /**
     * @var float|null
     */
    protected $timeout = null;

    /**
     * Socket constructor.
     *
     * @param $path
     * @param array $options associative array with keys from SocketOptions class
     */
    public function __construct($path, $options)
    {
        $this->path = $path;
        $this->applyOptions($options);
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
     *
     * @return $this
     * @throws SocketException;
     */
    public function create()
    {
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }

        // if socket is already created - do nothing
        if (isset($this->socket)) {
            return $this;
        }

        $this->socket = @socket_create($this->domain, $this->type, $this->protocol);
        $lastError = socket_last_error($this->socket);
        $errorMessage = socket_strerror($lastError);
        socket_clear_error($this->socket);
        if (!isset($this->socket)) {
            throw new SocketException("Cannot create socket. {$errorMessage}", $lastError);
        }

        if ($this->timeout > 0) {
            $success = true;
            $s = (int)$this->timeout;
            $ms = (int)(($this->timeout - $s) * 1000);
            $success &= socket_set_option($this->socket, $this->protocol, SO_RCVTIMEO, ['sec' => $s, 'usec' => $ms]);
            $success &= socket_set_option($this->socket, $this->protocol, SO_SNDTIMEO, ['sec' => $s, 'usec' => $ms]);

            if (!$success) {
                trigger_error('Cannot set socket timeout', E_USER_WARNING);
            }
        }

        return $this;
    }

    /**
     * Connect to socket
     *
     * @return $this
     * @throws SocketException
     */
    public function connect()
    {
        if ($this->debug) {
            echo __METHOD__ . PHP_EOL;
        }

        if (false === @socket_connect($this->socket, $this->path)) {
            $lastError = socket_last_error($this->socket);
            $errorMessage = socket_strerror($lastError);
            socket_clear_error($this->socket);
            throw new SocketException("Cannot connect socket to {$this->path}. {$errorMessage}", $lastError);
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
            echo __METHOD__ . "[DEBUG]" . PHP_EOL . $message . PHP_EOL . "[/DEBUG]" . PHP_EOL;
        }

        if (!isset($this->socket)) {
            throw new SocketException("Cannot write to empty socket.");
        }

        if (false === @socket_write($this->socket, $message, strlen($message))) {
            $lastError = socket_last_error($this->socket);
            $errorMessage = socket_strerror($lastError);
            socket_clear_error($this->socket);
            throw new SocketException("Error occur when write to stream. {$errorMessage}", $lastError);
        }

        return $this;
    }

    /**
     * Send $message to stream with flag set
     * @param string $message
     * @param int $flag socket_send $flag parameter
     *
     * @return $this
     * @throws SocketException
     */
    public function send($message = "", $flag = MSG_EOR)
    {
        if ($this->debug) {
            echo __METHOD__ . "[DEBUG]" . PHP_EOL . $message . PHP_EOL . "[/DEBUG]" . PHP_EOL;
        }

        if (!isset($this->socket)) {
            throw new SocketException("Cannot send data to empty socket.");
        }

        if (false === @socket_send($this->socket, $message, strlen($message), $flag)) {
            $lastError = socket_last_error($this->socket);
            $errorMessage = socket_strerror($lastError);
            socket_clear_error($this->socket);
            throw new SocketException("Error occur when write to stream. {$errorMessage}", $lastError);
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
            $lastError = socket_last_error($this->socket);
            $errorMessage = socket_strerror($lastError);
            socket_clear_error($this->socket);
            throw new SocketException("Error occur when read from stream: {$errorMessage}", $lastError);
        }
        return $partial;
    }

    protected function applyOptions($options)
    {
        if (isset($options[SocketOptions::SOCKET_DOMAIN])) {
            $this->domain = (int)$options[SocketOptions::SOCKET_DOMAIN];
        }
        if (isset($options[SocketOptions::SOCKET_PROTOCOL])) {
            $this->protocol = (int)$options[SocketOptions::SOCKET_PROTOCOL];
        }
        if (isset($options[SocketOptions::SOCKET_TYPE])) {
            $this->type = (int)$options[SocketOptions::SOCKET_TYPE];
        }
        if (isset($options[SocketOptions::SOCKET_TIMEOUT])) {
            $this->timeout = (float)$options[SocketOptions::SOCKET_TIMEOUT];
        }
        if (isset($options[SocketOptions::SOCKET_DEBUG])) {
            $this->debug = (bool)$options[SocketOptions::SOCKET_DEBUG];
        }

        return $this;
    }
}