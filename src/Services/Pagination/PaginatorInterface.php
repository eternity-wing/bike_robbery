<?php


namespace App\Services\Pagination;

use Doctrine\ORM\Query;

/**
 * Interface PaginatorInterface
 * @package App\Services\Pagination
 * @author Wings <eternity.mr8@gmail.com>
 */
interface PaginatorInterface
{
    /**
     * @param Query $query
     * @param int $currentPage
     * @param int $limit
     * @return array
     */
    public function paginate(Query $query, int $currentPage, int $limit): array;
}
