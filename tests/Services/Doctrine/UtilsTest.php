<?php


namespace App\Tests\Services\Doctrine;

use App\Entity\Police;
use App\Exception\TransactionException;
use App\Services\Doctrine\Utils as DoctrineUtils;
use App\Services\Utils;
use App\Tests\BaseKernelTest;

class UtilsTest extends BaseKernelTest
{
    private $doctrineUtils;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->doctrineUtils = new DoctrineUtils($this->entityManager);
    }


    public function testExecuteCallableInTransaction()
    {
        do {
            $randomPersonalCode = Utils::generateRandomString(20);
            $police = $this->entityManager->getRepository(Police::class)->findOneBy(['personalCode' => $randomPersonalCode]);
        } while ($police);

        $police = new Police();
        $police->setFullName("officer-{$randomPersonalCode}");
        $police->setPersonalCode("PCD-{$randomPersonalCode}");


        $this->doctrineUtils->executeCallableInTransaction(static function (\Doctrine\ORM\EntityManager $entityManager) use ($police) {
            $entityManager->persist($police);
        });
        $this->entityManager->refresh($police);
        $this->expectException(TransactionException::class);
        $duplicatePolice = clone $police;

        $this->doctrineUtils->executeCallableInTransaction(static function (\Doctrine\ORM\EntityManager $entityManager) use ($duplicatePolice) {
            $entityManager->persist($duplicatePolice);
        });
    }
}
