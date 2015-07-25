<?php

namespace Vifeed\TagBundle\Repository;

use Vifeed\TagBundle\Entity\Tag;
use DoctrineExtensions\Taggable\Entity\TagRepository as BaseTagRepository;

/**
 * TagRepository
 */
class TagRepository extends BaseTagRepository
{
    const MAX_RESULTS = 5;
    const CACHE_LIFETIME = 10800;


    /**
     * Поиск тегов по названию или его части с начала
     *
     * @param string $word
     * @param bool   $strict
     *
     * @return Tag[]
     */
    public function findByWord($word, $strict = false)
    {
        $qb = $this->createQueryBuilder('t');
        if ($strict) {
            $qb->where('t.name = :word')
               ->setParameter('word', $word);
        } else {
            $qb->where('t.name LIKE :word')
               ->setMaxResults(self::MAX_RESULTS)
               ->setParameter('word', $word . '%');
        }

        $query = $qb->getQuery()
                    ->useResultCache(true, self::CACHE_LIFETIME, 'tags:findByWord:' . $word);

        return $query->getResult();
    }
}
