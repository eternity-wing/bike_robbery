<?php

namespace App\DataFixtures;

use App\Entity\Bike;
use App\Entity\Police;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppFixtures
 * @package App\DataFixtures
 */
class AppFixtures extends Fixture
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * AppFixtures constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        $this->loadPolices($manager);
        $manager->flush();
        $this->loadBikes($manager);
        $manager->flush();
    }


    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function loadPolices(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 100; $i++) {
            $police = new Police();
            $pCodeSuffix = random_int(1, 1000000);
            $police->setPersonalCode('PC-' . $pCodeSuffix);
            $police->setFullName("Officer-{$pCodeSuffix}");
            $manager->persist($police);
        }
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function loadBikes(ObjectManager $manager): void
    {
        $colorsCount = count(Bike::AVAILABLE_COLORS);
        $typesCount = count(Bike::AVAILABLE_TYPES);
        for ($i = 1; $i <= 95; $i++) {
            $bike = new Bike();
            $bike->setColor(Bike::AVAILABLE_COLORS[random_int(0, $colorsCount - 1)]);
            $bike->setType(Bike::AVAILABLE_TYPES[random_int(0, $typesCount - 1)]);
            $randomCodeSuffix = random_int(1, 1000000);
            $bike->setLicenseNumber('LN-' . $randomCodeSuffix);
            $bike->setOwnerFullName('owner-' . $randomCodeSuffix);
            $randomDate = new \DateTime();
            $randomDate->setTimestamp(random_int(1, time()));
            $bike->setStealingDate($randomDate);
            $bike->setStealingDescription('description-' . $randomCodeSuffix);
            $manager->persist($bike);

            $availableResponsible = (random_int(1, 3) % 3) !== 0 ? null : $manager->getRepository(Police::class)->findOneBy(['isAvailable' => true]);
            if($availableResponsible){
                $bike->setResponsible($availableResponsible);
                $availableResponsible->setIsAvailable(false);
                $manager->flush();
            }
        }
    }

}
