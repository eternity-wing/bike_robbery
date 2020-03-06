<?php

namespace App\Repository;

use App\Entity\Police;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Police|null find($id, $lockMode = null, $lockVersion = null)
 * @method Police|null findOneBy(array $criteria, array $orderBy = null)
 * @method Police[]    findAll()
 * @method Police[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PoliceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Police::class);
    }


    /**
     * @return Query
     */
    public function findAllQuery():Query
    {
        $qb = $this->createQueryBuilder('p');
        $qb->orderBy('p.id', 'DESC');
        return $qb->getQuery();
    }

    // /**
    //  * @return Police[] Returns an array of Police objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Police
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
