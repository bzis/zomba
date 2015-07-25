<?php

namespace Vifeed\TagBundle\Manager;

use DoctrineExtensions\Taggable\Taggable;
use DoctrineExtensions\Taggable\TagManager as BaseTagManager;

/**
 * Class TagManager
 *
 * @package Vifeed\TagBundle\Manager
 */
class TagManager extends BaseTagManager
{
    /**
     * {@inheritdoc}
     *
     * добавлено инвалидация кеша
     */
    public function saveTagging(Taggable $resource)
    {
        $cacheDriver = $this->em->getConfiguration()->getResultCacheImpl();

        $oldTags = $this->getTagging($resource);
        $newTags = $resource->getTags()->toArray();

        $allTags = [];

        if (!is_array($oldTags)) {
            $oldTags = [];
        }

        $tmpTags = array_merge($oldTags, $newTags);
        foreach ($tmpTags as $tag) {
            $allTags[] = $tag->getName();
        }

        $allTags = array_unique($allTags);

        foreach ($allTags as $tag) {
            $len = mb_strlen($tag, 'UTF8');
            for ($i = 1; $i <= $len; $i++) {
                $str = mb_substr($tag, 0, $i, 'UTF8');
                $cacheDriver->delete('tags:findByWord:' . $str);
            }
        }

        parent::saveTagging($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function loadOrCreateTags(array $names)
    {
        foreach ($names as $key => $tag) {
            $names[$key] = mb_strtolower($tag, 'UTF8');
        }
        return parent::loadOrCreateTags($names);
    }


}