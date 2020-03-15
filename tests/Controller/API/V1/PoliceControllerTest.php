<?php


namespace App\Tests\Controller\API\V1;

use App\Entity\Bike;
use App\Entity\Police;
use App\Services\Utils;
use App\Tests\Controller\API\APIBaseKernelTest;

class PoliceControllerTest extends APIBaseKernelTest
{
    public function testGetBikes()
    {
        $response = $this->client->request('GET', $this->prependBaseApiUrl('bikes'));
        $this->assertEquals($response->getStatusCode(), 200);
    }


    public function testShouldCreatePoliceSuccessfully()
    {
        $data = [
            'personalCode' => 'PC-' . Utils::generateRandomString(5),
            'fullName' => 'officer ' . Utils::generateRandomString(5)
        ];
        $response = $this->client->request('post', $this->prependBaseApiUrl('polices'), [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($data)
        ]);
        $this->assertEquals($response->getStatusCode(), 201);
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($data, ['personalCode' => $responseData['personal_code'] , 'fullName' => $responseData['full_name']]);
    }


    public function testShouldFailureInCreatePolice()
    {
        $data = [
            'personalCode' => 'PC-' . Utils::generateRandomString(5),
        ];
        $response = $this->client->request('post', $this->prependBaseApiUrl('polices'), [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($data)
        ]);
        $responseContent = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('fullName', $responseContent['error']);

        $statusCode = $response->getStatusCode();
        $this->assertGreaterThanOrEqual(400, $statusCode);
        $this->assertLessThan(500, $statusCode);
    }


    public function testShouldPreventCreatingDuplicatePolice()
    {
        $police = $this->entityManager->getRepository(Police::class)->findOneBy([]);
        $data = [
            'personalCode' => $police->getPersonalCode(),
            'fullName' => 'officer ' . Utils::generateRandomString(5)
        ];
        $response = $this->client->request('post', $this->prependBaseApiUrl('polices'), [
            'headers' => ['Content-type' => 'application/json'],
            'body' => json_encode($data)
        ]);
        $responseContent = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('personalCode', $responseContent['error']);

        $statusCode = $response->getStatusCode();
        $this->assertGreaterThanOrEqual(400, $statusCode);
        $this->assertLessThan(500, $statusCode);
    }

    public function testShouldGetPolice()
    {
        $police = $this->entityManager->getRepository(Police::class)->findOneBy([]);
        if ($police) {
            $response = $this->client->get($this->prependBaseApiUrl("polices/{$police->getId()}"));
            $responseContent = json_decode($response->getBody()->getContents(), true);
            $this->assertEquals($responseContent['id'], $police->getId());
            $this->assertEquals($response->getStatusCode(), 200);
        }
        $this->assertTrue(true);
    }


    public function testShouldDeleteUnAvailablePolice()
    {
        $police = $this->entityManager->getRepository(Police::class)->findOneBy(['isAvailable' => false]);
        if ($police === null) {
            $this->assertTrue(true);
            return;
        }
        $availableAssignedBike = $this->entityManager->getRepository(Bike::class)->findOneBy(['responsible' => $police, 'isResolved' => false]);

        $response = $this->client->delete($this->prependBaseApiUrl("polices/{$police->getId()}"));

        $this->assertEquals($response->getStatusCode(), 204);

        if ($availableAssignedBike) {
            $this->entityManager->refresh($availableAssignedBike);
            $this->assertNull($availableAssignedBike->getResponsible());
        }
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
