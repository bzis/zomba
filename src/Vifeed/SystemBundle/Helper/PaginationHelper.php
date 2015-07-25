<?php

namespace Vifeed\SystemBundle\Helper;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class PaginationHelper
 *
 * @package Vifeed\SystemBundle\Helper
 */
class PaginationHelper
{
    /** @var Paginator */
    private $resource;
    private $perPage;
    private $page;

    /**
     * @param Query|QueryBuilder $query
     * @param int                $perPage
     * @param int                $page
     */
    public function __construct($query, $perPage, $page)
    {
        $paginator = new Paginator($query);
        $paginator->getQuery()
                  ->setFirstResult($perPage * ($page - 1))
                  ->setMaxResults($perPage);

        $this->resource = $paginator;
        $this->page = $page;
        $this->perPage = $perPage;
    }

    /**
     * массив элементов пагинатора
     *
     * @return array
     */
    public function getItemsArray()
    {
        return $this->resource->getIterator()->getArrayCopy();
    }

    /**
     * @param Router $router
     * @param string $routeName
     * @param array  $routeParams
     *
     * @return array
     */
    public function getLinkHeader(Router $router, $routeName, $routeParams)
    {
        $links = [];
        $totalPages = ceil($this->resource->count() / $this->perPage);

        if ($this->page > 1) {
            $links['first'] = $router->generate($routeName, $routeParams + ['page' => 1]);
            $links['prev'] = $router->generate($routeName, $routeParams + ['page' => $this->page - 1]);
        }
        if ($this->page < $totalPages) {
            $links['next'] = $router->generate($routeName, $routeParams + ['page' => $this->page + 1]);
            $links['last'] = $router->generate($routeName, $routeParams + ['page' => $totalPages]);
        }

        if (!empty($links)) {
            $linksHeader = [];
            foreach ($links as $name => $link) {
                $linksHeader[] = '<' . $link . '>; rel="' . $name . '"';
            }

            return ['Link' => join(', ', $linksHeader)];
        }

        return [];
    }
} 