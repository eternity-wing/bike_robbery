<?php


namespace App\Tests\Factory;

use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\InvalidObjectException;
use App\Factory\BikeFactory;
use App\Factory\PoliceFactory;
use App\Services\Doctrine\Utils as DoctrineUtils;
use App\Services\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BikeFactoryTest extends KernelTestCase
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

    public function testCreateBike()
    {
        $uniqueLicenseNumber = $this->findUniqueLicenseNumber();
        $data = [
            'licenseNumber' => $uniqueLicenseNumber,
            'ownerFullName' => "owner-{$uniqueLicenseNumber}",
            'isResolved' => false,
            'color' => 'red',
            'stealingDescription' => 'stealing in test',
            'stealingDate' => new \DateTime('now'),
            'responsible' => null,
            'type' => 'sport'
        ];
        $bike = $this->bikeFactory->create($data);


        $createdBikeData = [
            'licenseNumber' => $bike->getLicenseNumber(),
            'ownerFullName' => $bike->getOwnerFullName(),
            'isResolved' => $bike->getIsResolved(),
            'color' => $bike->getColor(),
            'stealingDescription' => $bike->getStealingDescription(),
            'stealingDate' => $bike->getStealingDate(),
            'responsible' => $bike->getResponsible(),
            'type' => $bike->getType()
        ];
        $this->assertEquals($createdBikeData, $data);

        $this->expectException(InvalidObjectException::class);
        $bike1 = $this->bikeFactory->create($data);
    }

    public function testAssigningResponsible()
    {
        $availableBike = $this->entityManager->getRepository(Bike::class)->findOneBikeNeedsResponsible();
        $availablePolice = $this->entityManager->getRepository(Police::class)->findOneBy(['isAvailable' => true]);


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

        if ($availablePolice === null) {
            $uniqueRandomPersonalCode = $this->findUniquePersonalCode();
            $policeData = ['personalCode' => $uniqueRandomPersonalCode, 'fullName' => "officer-{$uniqueRandomPersonalCode}", 'isAvailable' => true];
            $availablePolice = $this->policeFactory->create($policeData);
        }

        $this->bikeFactory->assignResponsible($availableBike, null);

        $responsiblePolice = $availableBike->getResponsible();
        $this->assertInstanceOf(Police::class, $responsiblePolice);

        $this->bikeFactory->assignResponsible($availableBike, null);

        $reassignedResponsiblePolice = $availableBike->getResponsible();

        $this->assertEquals($responsiblePolice, $reassignedResponsiblePolice);
    }

    public function testResolvingBike()
    {
        $notResolvedBike = $this->entityManager->getRepository(Bike::class)
            ->findOneBy(['isResolved' => false]);

        if ($notResolvedBike === null) {
            $uniqueRandomPersonalCode = $this->findUniquePersonalCode();
            $policeData = ['personalCode' => $uniqueRandomPersonalCode, 'fullName' => "officer-{$uniqueRandomPersonalCode}", 'isAvailable' => true];
            $availablePolice = $this->policeFactory->create($policeData);

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
            $notResolvedBike = $this->bikeFactory->create($bikeData);
            $this->bikeFactory->assignResponsible($notResolvedBike, null);
        }

        $responsiblePolice = $notResolvedBike->getResponsible();
        $this->assertFalse($notResolvedBike->getIsResolved());
        $this->assertInstanceOf(Police::class, $responsiblePolice);

        $this->bikeFactory->resolve($notResolvedBike, null);

        $this->assertInstanceOf(Police::class, $notResolvedBike->getResponsible());
        $this->assertTrue($notResolvedBike->getIsResolved());
    }


    public function testDeleteBikeWhichHasResponsibleAndNotResolvedYet()
    {
        $notResolvedBike = $this->entityManager->getRepository(Bike::class)
            ->findOneBy(['isResolved' => false]);

        if ($notResolvedBike === null) {
            $uniqueRandomPersonalCode = $this->findUniquePersonalCode();
            $policeData = ['personalCode' => $uniqueRandomPersonalCode, 'fullName' => "officer-{$uniqueRandomPersonalCode}", 'isAvailable' => true];
            $availablePolice = $this->policeFactory->create($policeData);

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
            $notResolvedBike = $this->bikeFactory->create($bikeData);
            $this->bikeFactory->assignResponsible($notResolvedBike, null);
        }

        $responsiblePolice = $notResolvedBike->getResponsible();
        $this->assertFalse($notResolvedBike->getIsResolved());
        $this->assertInstanceOf(Police::class, $responsiblePolice);

        $this->bikeFactory->delete($notResolvedBike, null);

        $this->entityManager->refresh($responsiblePolice);
        $this->assertTrue($responsiblePolice->getIsAvailable());
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
}
