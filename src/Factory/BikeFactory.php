<?php


namespace App\Factory;

use App\Entity\Bike;
use App\Entity\Police;
use App\Services\Doctrine\Utils as DoctrineUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Exception\TransactionException;
use Doctrine\DBAL\ConnectionException;

/**
 * Class BikeFactory
 * @package App\Factory
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class BikeFactory extends BaseFactory
{
    /**
     * @var DoctrineUtils
     */
    private $doctrineUtils;

    /**
     * PoliceFactory constructor.
     * @param EntityManagerInterface $manager
     * @param ContainerInterface $container
     * @param DoctrineUtils $doctrineUtils
     */
    public function __construct(EntityManagerInterface $manager, ContainerInterface $container, DoctrineUtils $doctrineUtils)
    {
        parent::__construct($manager, $container);
        $this->doctrineUtils = $doctrineUtils;
    }

    /**
     * @param Bike $bike
     */
    public function store(Bike $bike)
    {
        $this->entityManager->persist($bike);
        $this->entityManager->flush();
    }

    /**
     * @param Bike $bike
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function assignResponsible(Bike $bike, ?callable $exceptionCallback): void
    {
        try {
            $this->doctrineUtils->executeCallableInTransaction(static function (EntityManagerInterface $entityManager) use ($bike) {
                $availableOfficer = $this->entityManager->getRepository(Police::class)->findOneBy(['isAvailable' => true]);
                if ($availableOfficer instanceof Police) {
                    $availableOfficer->setIsAvailable(false);
                    $bike->setResponsible($availableOfficer);
                }
            });
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
        $this->entityManager->refresh($bike);
    }

    /**
     * @param Bike $bike
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function resolve(Bike $bike, ?callable $exceptionCallback): void
    {
        try {
            $this->doctrineUtils->executeCallableInTransaction(static function (EntityManagerInterface $entityManager) use ($bike) {
                $responsibleOfficer = $bike->getResponsible();
                $isNotEngagedOfficer = $bike->getIsResolved() || $responsibleOfficer === null;
                if ($isNotEngagedOfficer) {
                    return;
                }
                $entityManager->persist($responsibleOfficer);
                $bike->setIsResolved(true);
                $responsibleOfficer->setIsAvailable(true);
            });
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
        $this->entityManager->refresh($bike);
    }

    /**
     * @param Bike $bike
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function delete(Bike $bike, ?callable $exceptionCallback): void
    {
        try {
            $this->doctrineUtils->executeCallableInTransaction(static function (EntityManagerInterface $entityManager) use ($bike) {
                $responsibleOfficer = $bike->getResponsible();
                $isNotEngagedOfficer = $bike->getIsResolved() || $responsibleOfficer === null;
                if ($isNotEngagedOfficer) {
                    $entityManager->persist($responsibleOfficer);
                    $responsibleOfficer->setIsAvailable(true);
                }
            });
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
        $this->entityManager->remove($bike);
    }
}
