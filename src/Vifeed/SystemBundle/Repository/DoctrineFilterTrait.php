<?php

namespace Vifeed\SystemBundle\Repository;

use Doctrine\ORM\EntityManager;

/**
 * trait DoctrineFilterTrait
 * можно подключать к классам EntityRepository
 *
 * @package Vifeed\SystemBundle\Repository
 */
trait DoctrineFilterTrait
{
    /**
     * Найти запись, даже если она soft-deleted
     */
    public function findWithoutFilter($id)
    {
        $this->disableSoftDeleteableFilter();
        $object = $this->find($id);
        $this->enableSoftDeleteableFilter();
        return $object;
    }

    /**
     * отключить soft-deleteable filter для entity
     *
     * @return void
     */
    private function disableSoftDeleteableFilter()
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $filter = $em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity($this->_entityName);
    }

    /**
     * включить soft-deleteable filter для entity
     *
     * @return void
     */
    private function enableSoftDeleteableFilter()
    {
        $filter = $this->getEntityManager()->getFilters()->getFilter('softdeleteable');
        $filter->enableForEntity($this->_entityName);
    }
} 