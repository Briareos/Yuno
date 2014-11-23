<?php

namespace Yuno\MainBundle\Paginator;

use Zend\Paginator;
use Doctrine\ORM\Query;

class PaginatorHelper
{
    public static function getPaginator($totalResults = 0, $page = 1, $itemsPerPage = 30, $pageRange = 10)
    {
        $paginatorAdapter        = new Paginator\Adapter\Null($totalResults);
        $paginator               = new Paginator\Paginator($paginatorAdapter);
        $paginatorScrollingStyle = new Paginator\ScrollingStyle\Sliding();
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($itemsPerPage);
        $paginator->setPageRange($pageRange);
        $paginator->setDefaultScrollingStyle($paginatorScrollingStyle);

        return $paginator;
    }

    public static function applyOffsetAndLimit(Query $query, Paginator\Paginator $paginator)
    {
        $query->setFirstResult(($paginator->getCurrentPageNumber() - 1) * $paginator->getItemCountPerPage())
            ->setMaxResults($paginator->getItemCountPerPage());
    }
}