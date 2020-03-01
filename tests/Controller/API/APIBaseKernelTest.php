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

    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    protected function generateRandomString(int $length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}