<?php
require(__DIR__ .'/ServerHandler.php');

$handler = new ServerHandler();
if (isset($_GET['method']) && isset($_GET['value'])) {
    $method = $_GET['method'];
    $value = $_GET['value'];
    $handler->$method($value);
}

$handler->response();