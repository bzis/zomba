<?php

namespace Vifeed\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Vifeed\PlatformBundle\Entity\PlatformType;

class LoadPlatformTypeData implements FixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = array('Сайт', 'Игра', 'Мобильное приложение', 'Социальная сеть');
        $models = array();

        foreach ($types as $r) {
            $type = new PlatformType();
            $type->setName($r);
            $models[] = $type;
            $manager->persist($type);
        }

        $manager->flush();
    }
}
