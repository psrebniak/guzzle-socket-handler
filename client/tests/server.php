<?php


$post = ($_SERVER["CONTENT_TYPE"] === "application/json")
    ? json_decode(file_get_contents('php://input'), true)
    : $_POST;

if (isset($_GET['redirects']) && $_GET['redirects'] > 0) {
    $redirects = $_GET['redirects'] - 1;
    header("Location: http://localhost:8080/?redirects={$redirects}");
    die;
}

header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'Content-Type' => $_SERVER["CONTENT_TYPE"],
    'get' => $_GET,
    'post' => $post,
    'files' => array_map(function ($object) {
        return $object['name'] = md5_file($object['tmp_name']);
    }, $_FILES)
]);
