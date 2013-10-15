<?php

namespace Vifeed\SystemBundle\Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase {

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected static $client;

    /** @var \Symfony\Component\Routing\Router */
    protected static $router;

    /** @var  EntityManager */
    protected static $em;

    protected static $parameters = array();

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$router = self::$client->getContainer()->get('router');
        self::$em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
    }


}
