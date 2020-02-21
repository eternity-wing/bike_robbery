<?php


namespace App\Services\Pagination;


use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Class Paginator
 * @package App\Services\Pagination
 * @author Wings <eternity.mr8@gmail.com>
 */
class Paginator implements PaginatorInterface
{

    /**
     * @param Query $query
     * @param int $currentPage
     * @param int $limit
     * @return array
     */
    public function paginate(Query $query, int $currentPage, int $limit): array
    {
        $paginator = new DoctrinePaginator($query);

        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $limit);

        $result = $paginator->getQuery()->setFirstResult($limit * ($currentPage - 1))
            ->setMaxResults($limit)->getResult();

        return ['totalItems' => $totalItems, 'pageCount' => $pagesCount, 'items' => $result];

    }
}