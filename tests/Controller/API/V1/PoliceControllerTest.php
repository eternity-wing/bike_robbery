<?php


namespace App\Tests\Controller\API\V1;


use App\Entity\Police;
use App\Tests\Controller\API\APIBaseKernelTest;
use GuzzleHttp\Client;

class PoliceControllerTest extends APIBaseKernelTest
{

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $response = $this->client->request('GET', $url);
        $this->assertEquals($response->getStatusCode(), 200);
    }


    public function testShouldCreatePoliceSuccessfully()
    {
        $data = [
            'personalCode' => 'PC-' . $this->generateRandomString(5),
            'fullName' => 'officer ' . $this->generateRandomString(5)
        ];
        $response = $this->client->request('post', $this->prependBaseApiUrl('polices'), [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($data)
        ]);
        $this->assertEquals($response->getStatusCode(), 201);
    }


    public function testShouldFailureInCreatePolice()
    {
        $data = [
            'personalCode' => 'PC-' . $this->generateRandomString(5),
        ];
        $response = $this->client->request('post', $this->prependBaseApiUrl('polices'), [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($data)
        ]);
        $responseContent = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('fullName', $responseContent);

        $statusCode = $response->getStatusCode();
        $this->assertGreaterThanOrEqual(400, $statusCode);
        $this->assertLessThan(500, $statusCode);
    }


    public function testShouldPreventCreatingDuplicatePolice()
    {
        $police = $this->entityManager->getRepository(Police::class)->findOneBy([]);
        $data = [
            'personalCode' => $police->getPersonalCode(),
            'fullName' => 'officer ' . $this->generateRandomString(5)
        ];
        $response = $this->client->request('post', $this->prependBaseApiUrl('polices'), [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($data)
        ]);
        $responseContent = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('personalCode', $responseContent);

        $statusCode = $response->getStatusCode();
        $this->assertGreaterThanOrEqual(400, $statusCode);
        $this->assertLessThan(500, $statusCode);
    }

//    public function testShouldGetPoliceFromApi()
//    {
//        $police = $this->entityManager->getRepository(Police::class)->findOneBy([]);
//        $response = $this->client->get($this->prependBaseApiUrl("polices/{$police->getId()}"));
//        $responseContent = json_decode($response->getBody()->getContents(), true);
//        $this->assertEquals($responseContent['id'], $police->getId());
//        $this->assertEquals($response->getStatusCode(), 200);
//
//    }

    /**
     * @return \Generator
     */
    public function urlProvider(): \Generator
    {
        yield [$this->prependBaseApiUrl('bikes')];
        yield [$this->prependBaseApiUrl('polices')];
    }


    /**
     * @param string|null $url
     * @return string
     */
    private function prependBaseApiUrl(?string $url): string
    {
        return '/api/v1/' . $url;
    }
}