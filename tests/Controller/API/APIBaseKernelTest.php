<?php


namespace App\Tests\Controller\API;

use App\Tests\BaseKernelTest;
use GuzzleHttp\Client;

class APIBaseKernelTest extends BaseKernelTest
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->client = new Client([
            'base_uri' => self::$container->getParameter('PROJECT_BASE_URL'),
            'http_errors' => false
        ]);
    }
}
