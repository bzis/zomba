<?php

namespace Vifeed\SystemBundle\Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestCase extends WebTestCase
{

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected static $client;

    /** @var \Symfony\Component\Routing\Router */
    protected static $router;

    /** @var  EntityManager */
    protected static $em;

    protected static $parameters = array();

    /** @var  \Symfony\Component\DependencyInjection\ContainerInterface */
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$client = static::createClient();
        static::$router = self::$client->getContainer()->get('router');
        static::$em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        static::$container = self::getContainer();

    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public static function getContainer()
    {
        if (static::$kernel->getContainer() === null) {
            static::$kernel->boot();
        }

        return static::$kernel->getContainer();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return static::$container->get('doctrine.orm.entity_manager');
    }


}
