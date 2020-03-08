<?php


namespace App\Tests\Factory;

use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\InvalidObjectException;
use App\Factory\BikeFactory;
use App\Factory\PoliceFactory;
use App\Services\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Services\Doctrine\Utils as DoctrineUtils;

/**
 * Class PoliceFactoryTest
 * @package App\Tests\Factory
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class PoliceFactoryTest extends KernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PoliceFactory
     */
    private $policeFactory;

    /**
     * @var BikeFactory
     */
    private $bikeFactory;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $doctrineUtils = new DoctrineUtils($this->entityManager);
        $validator = $kernel->getContainer()->get('validator');
        $this->policeFactory = new PoliceFactory($this->entityManager, self::$container, $validator, $doctrineUtils);
        $this->bikeFactory = new BikeFactory($this->entityManager, self::$container, $validator, $doctrineUtils);
    }


    public function testCreatePolice()
    {
        $uniqueRandomPersonalCode = $this->findUniquePersonalCode();

        $data = ['personalCode' => $uniqueRandomPersonalCode, 'fullName' => "officer-{$uniqueRandomPersonalCode}", 'isAvailable' => true];
        $police = $this->policeFactory->create($data);
        $createdPoliceData = [
            'personalCode' => $police->getPersonalCode(),
            'fullName' => $police->getFullName(),
            'isAvailable' => $police->getIsAvailable()
        ];
        $this->assertEquals($createdPoliceData, $data);

        $this->expectException(InvalidObjectException::class);
        $police1 = $this->policeFactory->create($data);
    }

    public function testAssigningResponsibility()
    {
        $availablePolice = $this->entityManager->getRepository(Police::class)->findOneBy(['isAvailable' => true]);
        $availableBike = $this->entityManager->getRepository(Bike::class)->findOneBikeNeedsResponsible();

        if ($availablePolice === null) {
            $uniqueRandomPersonalCode = $this->findUniquePersonalCode();
            $policeData = ['personalCode' => $uniqueRandomPersonalCode, 'fullName' => "officer-{$uniqueRandomPersonalCode}", 'isAvailable' => true];
            $availablePolice = $this->policeFactory->create($policeData);
        }

        if ($availableBike === null) {
            $uniqueLicenseNumber = $this->findUniqueLicenseNumber();
            $bikeData = [
                'licenseNumber' => $uniqueLicenseNumber,
                'ownerFullName' => "owner-{$uniqueLicenseNumber}",
                'isResolved' => false,
                'color' => 'red',
                'stealingDescription' => 'stealing in test',
                'stealingDate' => new \DateTime('now'),
                'responsible' => null
            ];
            $availableBike = $this->bikeFactory->create($bikeData);
        }


        $this->policeFactory->assignResponsibility($availablePolice, null);

        $assignedBike = $availableBike = $this->entityManager->getRepository(Bike::class)
            ->findOneBy(['responsible' => $availablePolice, 'isResolved' => false]);

        $this->assertInstanceOf(Bike::class, $assignedBike);

        $this->policeFactory->assignResponsibility($availablePolice, null);

        $reAssignedBike = $availableBike = $this->entityManager->getRepository(Bike::class)
            ->findOneBy(['responsible' => $availablePolice, 'isResolved' => false]);

        $this->assertEquals($assignedBike, $reAssignedBike);
    }

    public function testDeleteUnavailablePolice()
    {
        $unavailablePolice = $this->entityManager->getRepository(Police::class)->findOneBy(['isAvailable' => false]);

        if ($unavailablePolice === null) {
            $uniqueRandomPersonalCode = $this->findUniquePersonalCode();
            $policeData = ['personalCode' => $uniqueRandomPersonalCode, 'fullName' => "officer-{$uniqueRandomPersonalCode}", 'isAvailable' => false];
            $unavailablePolice = $this->policeFactory->create($policeData);
        }

        $this->policeFactory->delete($unavailablePolice, null);

        $notResolvedAssignedBNike = $this->entityManager->getRepository(Bike::class)
            ->findOneBy(['responsible' => $unavailablePolice, 'isResolved' => false]);

        $this->assertNull($notResolvedAssignedBNike);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function findUniquePersonalCode(): string
    {
        do {
            $randomString = Utils::generateRandomString(20);
            $randomPersonalCode = "PCD-{$randomString}";
            $police = $this->entityManager->getRepository(Police::class)->findOneBy(['personalCode' => $randomPersonalCode]);
        } while ($police);
        return $randomPersonalCode;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function findUniqueLicenseNumber(): string
    {
        do {
            $randomString = Utils::generateRandomString(10);
            $randomLicenseNumber = "LN-{$randomString}";
            $bike = $this->entityManager->getRepository(Bike::class)->findOneBy(['licenseNumber' => $randomLicenseNumber]);
        } while ($bike);
        return $randomLicenseNumber;
    }



    public function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
