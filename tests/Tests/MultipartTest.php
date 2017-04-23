<?php

namespace psrebniak\GuzzleSocketHandler\Tests;

use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;

class MultipartTest extends TestCase
{
    /** @var  \GuzzleHttp\Client */
    protected $client;
    protected $fileContent = null;

    public function setUp()
    {
        $this->client = \psrebniak\GuzzleSocketHandler\getClient();
    }

    public function testMultiPart()
    {
        $request = $this->client->post('/?method=files&value=filename', [
            RequestOptions::MULTIPART => $this->getMultiPartJson()
        ]);
        $json = \GuzzleHttp\json_decode($request->getBody(), true);
        self::assertEquals(md5($this->getFileContent()), $json['files'], "Returned file hash equals");
    }

    protected function getFileContent()
    {
        if (isset($this->fileContent)) {
            return $this->fileContent;
        }
        $this->fileContent = file_get_contents(__FILE__);
        if (!$this->fileContent) {
            throw new \PHPUnit_Framework_IncompleteTestError("Cannot read file");
        }
        return $this->fileContent;
    }

    protected function getMultiPartJson()
    {
        return [
            [
                'name' => 'foo',
                'contents' => 'data',
                'headers' => ['X-Baz' => 'bar']
            ],
            [
                'name' => 'filename',
                'contents' => $this->getFileContent(),
                'filename' => 'caller.php'
            ]
        ];
    }

}