<?php

namespace UnixSocketHandler;

class Socket
{
    const EOL = "\r\n";

    protected $socket = null;
    protected $path = null;

    protected $domain = null;
    protected $type = null;
    protected $protocol = null;

    protected $debug = false;
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
            $this->socket = socket_create($this->domain, $this->type, $this->protocol);
            if (!isset($this->socket)) {
                throw new SocketException("Cannot create socket");
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

        if (false === socket_connect($this->socket, $path)) {
            throw new SocketException("Cannot connect socket to {$path}");
        }
//        if (false === socket_set_nonblock($this->socket)) {
//            $this->close();
//            throw new SocketException("Cannot set socket as non blocking");
//        }

        return $this;
    }

    /**
     * Write to socket
     * @param string $payload
     *
     * @return $this
     * @throws SocketException
     */
    public function write($payload)
    {
        if ($this->debug) {
            $this->debugString .= $payload;
        }

        if (!isset($this->socket)) {
            throw new SocketException("Cannot write to empty socket" . PHP_EOL);
        }

        if (false === socket_write($this->socket, $payload, strlen($payload))) {
            throw new SocketException("Error occur when write to stream" . PHP_EOL);
        }

        return $this;
    }

    /**
     * Send $payload to stream with flag set
     * @param string $payload
     * @param int $flag socket_send $flag parameter
     *
     * @return $this
     * @throws SocketException
     */
    public function send($payload = "", $flag = MSG_EOR)
    {
        if ($this->debug) {
            $this->debugString .= $payload;
            echo "> " . str_replace("\n", "\n> ", $this->debugString) . PHP_EOL . PHP_EOL;
            $this->debugString = "";
        }

        if (!isset($this->socket)) {
            throw new SocketException("Cannot write to empty socket" . PHP_EOL);
        }

        if (false === socket_send($this->socket, $payload, strlen($payload), $flag)) {
            throw new SocketException("Error occur when send to stream" . PHP_EOL);
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
            echo __METHOD__ . PHP_EOL;
        }
        if (!isset($this->socket)) {
            throw new SocketException("Cannot read from empty socket" . PHP_EOL);
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

        if ($this->debug) {
            echo "< " . str_replace("\n", "\n< ", $response) . PHP_EOL . PHP_EOL;
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
}