<?php


namespace App\Services\Doctrine;

use App\Exception\TransactionException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Utils
 * @package App\Services\Doctrine\
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class Utils
{

    /**
     * @var EntityManagerInterface
     */
    public $entityManager;

    /**
     * Utils constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @param callable $callback
     * @throws TransactionException
     */
    public function executeCallableInTransaction(callable $callback): void
    {
        try {
            $this->entityManager->transactional(static function (EntityManagerInterface $em) use ($callback) {
                $callback($em);
            });
        } catch (\Exception $e) {
            throw new TransactionException($e->getMessage());
        }
    }
}
