<?php

use GuzzleHttp\RequestOptions;
use UnixSocketHandler\UnixSocketHandlerFactory;

$guzzle = new GuzzleHttp\Client([
    'handler' => new UnixSocketHandlerFactory(__DIR__ . '/../socat.sock'),
]);

$json = ['redirects' => 2];
$multipart = [
    [
        'name' => 'foo',
        'contents' => 'data',
        'headers' => ['X-Baz' => 'bar']
    ],
    [
        'name' => 'qux',
        'contents' => fopen('/usr/share/applications/file-roller.desktop', 'r'),
        'filename' => 'custom_filename.txt'
    ],
];

$form_params = [
    'field_name' => 'abc',
    'other_field' => '123',
    'nested_field' => [
        'nested' => 'hello'
    ]
];

$response = $guzzle
    ->get('https://limango.pl/?redirects=5', [
//        'debug' => true,
        'json' => $json,
//        'multipart' => $multipart,
//        'form_params' => $form_params,
        RequestOptions::ALLOW_REDIRECTS => [
            'max'             => 5,
            'strict'          => true,
            'referer'         => true,
            'track_redirects' => true,
        ],
    ]);

var_dump([
    $response->getStatusCode(),
    $response->getReasonPhrase(),
    $response->getBody()->getContents()
]);


