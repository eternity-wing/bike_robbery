<?php


namespace App\Services\Doctrine;

use App\Exception\TransactionException;
use Doctrine\ORM\EntityManagerInterface;

class Utils
{
    public $entityManager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @param callable $callback
     * @throws TransactionException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function executeCallableInTransaction(callable $callback): void
    {
        try {
            $callback();
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw new TransactionException($e->getMessage());
        }
    }
}
