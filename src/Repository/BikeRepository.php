<?php

namespace App\Repository;

use App\Entity\Bike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Bike|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bike|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bike[]    findAll()
 * @method Bike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bike::class);
    }

    /**
     * @return Bike|null
     */
    public function findOneBikeNeedsResponsible(): ?Bike
    {
        $qb = $this->createQueryBuilder('b');
        $qb->where('b.responsible IS NULL');
        $result = $qb->setMaxResults(1)->getQuery()->getResult();
        return $result[0] ?? null;
    }

    /**
     * @param array $filters
     * @return Query
     */
    public function getFilterableQuery(array $filters=[]): Query{
        $parameters = [];
        $qb = $this->createQueryBuilder('b');

        if(isset($filters['license_number'])){
            $qb->andWhere('b.licenseNumber = :license');
            $parameters['license'] = $filters['license_number'];
        }

        if(isset($filters['color'])){
            $qb->andWhere('b.color = :clr');
            $parameters['clr'] = $filters['color'];
        }

        if(isset($filters['owner_full_name'])){
            $qb->andWhere('b.ownerFullName = :ownerName');
            $parameters['ownerName'] = $filters['owner_full_name'];
        }

        if(isset($filters['license_number'])){
            $qb->andWhere('b.licenseNumber = :license');
            $parameters['license'] = $filters['license_number'];
        }

        if(isset($filters['responsibleCode'])){
            $qb->innerJoin('b.responsible', 'r');
            $qb->andWhere('r.personalCode = :pCode');
            $parameters['pCode'] = $filters['responsibleCode'];
        }

        if(isset($filters['stealingDate'])){
            $qb->andWhere('r.stealingDate >= :dateFrom AND r.stealingDate < :dateTo');
            $parameters['dateFrom'] = $filters['stealingDate']->setTime(0, 0, 1);
            $parameters['dateTo'] = (clone $filters['stealingDate'])->setTime(23, 59, 59);
        }




        return  $qb->setParameters($parameters)->getQuery();

    }

    // /**
    //  * @return Bike[] Returns an array of Bike objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Bike
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
