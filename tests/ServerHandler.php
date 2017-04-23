<?php

class ServerHandler
{
    protected $request = null;
    protected $query = null;

    protected $response = [];

    function __construct()
    {
        $this->request = (isset($_SERVER["CONTENT_TYPE"]) && "application/json" === $_SERVER["CONTENT_TYPE"])
            ? json_decode(file_get_contents('php://input'), true)
            : $_POST;

        $this->query = $_GET;
    }

    function authenticate()
    {
        $this->response['authenticate' ] = [
            'user' => $_SERVER['PHP_AUTH_USER'],
            'password' => $_SERVER['PHP_AUTH_PW']
        ];
    }

    public function sleep($value)
    {
        sleep((int)$value);
    }

    public function redirect($value)
    {
        $redirects = (int)$value - 1;
        header("Location: /?redirects={$redirects}");
        die;
    }

    public function files($value)
    {
        if (isset($_FILES[$value])) {
            $this->response['files'] = md5(file_get_contents($_FILES[$value]['tmp_name']));
        }
    }

    public function response()
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'method' => $_SERVER['REQUEST_METHOD'],
            'Content-Type' => isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : false,
            'get' => $this->query,
            'post' => $this->request
        ], $this->response));
    }

    function __call($name, $argument)
    {
        throw new \RuntimeException("Method not implemented");
    }
}