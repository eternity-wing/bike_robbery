<?php


namespace App\Factory;

use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\TransactionException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Services\Doctrine\Utils as DoctrineUtils;

/**
 * Class PoliceFactory
 * @package App\Factory
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class PoliceFactory extends BaseFactory
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
     * @param Police $police
     */
    public function store(Police $police)
    {
        $this->entityManager->persist($police);
        $this->entityManager->flush();
    }

    /**
     * @param Police $police
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function assignResponsibility(Police $police, ?callable $exceptionCallback): void
    {
        try {
            $bikeNeedsResponsible = $this->entityManager->getRepository(Bike::class)->findOneBikeNeedsResponsible();
            if ($bikeNeedsResponsible instanceof Bike) {
                $this->doctrineUtils->executeCallableInTransaction(static function () use ($police, $bikeNeedsResponsible) {
                    $police->setIsAvailable(false);
                    $bikeNeedsResponsible->setResponsible($police);
                });
                $this->entityManager->refresh($police);
            }
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
    }


    /**
     * @param Police $police
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function delete(Police $police, ?callable $exceptionCallback): void
    {
        try {
            $this->doctrineUtils->executeCallableInTransaction(static function () use ($police) {
                $noneResolveBike = $this->entityManager->getRepository(Bike::class)
                    ->findOneBy(['responsible' => $police, 'isResolved' => true]);
                if ($noneResolveBike instanceof Bike) {
                    $this->entityManager->persist($noneResolveBike);
                    $noneResolveBike->setResponsible(null);
                }

                $this->entityManager->remove($police);
            });
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
    }
}
