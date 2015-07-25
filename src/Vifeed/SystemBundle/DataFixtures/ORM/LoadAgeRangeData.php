<?php

namespace Vifeed\SystemBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Vifeed\SystemBundle\Entity\AgeRange;

class LoadAgeRangeData implements FixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $ranges = array('Under 13', '14-17', '18-24', '25-34', '35-44', '45-54', '55-64', 'Over 65');
        $models = array();

        foreach ($ranges as $r) {
            $range = new AgeRange();
            $range->setName($r);
            $models[] = $range;
            $manager->persist($range);
        }

        $manager->flush();
    }
}
