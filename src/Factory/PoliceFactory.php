<?php


namespace App\Factory;

use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\InvalidObjectException;
use App\Exception\TransactionException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Services\Doctrine\Utils as DoctrineUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function __construct(EntityManagerInterface $manager, ContainerInterface $container, ValidatorInterface $validator, DoctrineUtils $doctrineUtils)
    {
        parent::__construct($manager, $container, $validator);
        $this->doctrineUtils = $doctrineUtils;
    }


    /**
     * @param array $data
     * @return Police
     * @throws InvalidObjectException
     */
    public function create(array $data): Police
    {
        ['personalCode' => $personalCode, 'fullName' => $fullName, 'isAvailable' => $isAvailable] = $data;
        $police = new Police();
        $police->setPersonalCode($personalCode);
        $police->setFullName($fullName);
        $police->setIsAvailable($isAvailable);
        $this->validate($police);
        $this->store($police);
        return $police;
    }

    /**
     * @param Police $police
     */
    public function store(Police $police)
    {
        $this->entityManager->persist($police);
        $this->entityManager->flush();
        $this->entityManager->refresh($police);
    }

    /**
     * @param Police $police
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function assignResponsibility(Police $police, ?callable $exceptionCallback): void
    {
        try {
            $isResponsibilityAlreadyAssigned = !$police->getIsAvailable();
            if ($isResponsibilityAlreadyAssigned) {
                return;
            }
            $this->doctrineUtils->executeCallableInTransaction(static function (EntityManagerInterface $entityManager) use ($police) {
                $bikeNeedsResponsible = $entityManager->getRepository(Bike::class)->findOneBikeNeedsResponsible();
                if ($bikeNeedsResponsible instanceof Bike) {
                    $police->setIsAvailable(false);
                    $bikeNeedsResponsible->setResponsible($police);
                }
            });
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
        $this->entityManager->refresh($police);
    }


    /**
     * @param Police $police
     * @param callable|null $exceptionCallback
     * @throws ConnectionException
     */
    public function delete(Police $police, ?callable $exceptionCallback): void
    {
        try {
            $this->doctrineUtils->executeCallableInTransaction(static function (EntityManagerInterface $entityManager) use ($police) {
                $noneResolveBike = $entityManager->getRepository(Bike::class)
                    ->findOneBy(['responsible' => $police, 'isResolved' => false]);
                if ($noneResolveBike instanceof Bike) {
                    $entityManager->persist($noneResolveBike);
                    $noneResolveBike->setResponsible(null);
                }
            });
        } catch (TransactionException $e) {
            $exceptionCallback($e);
        }
        $this->entityManager->remove($police);
    }
}
