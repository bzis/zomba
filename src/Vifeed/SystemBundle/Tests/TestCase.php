<?php

namespace Vifeed\SystemBundle\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

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
    protected static function getContainer()
    {
        if (static::getKernel()->getContainer() === null) {
            static::getKernel()->boot();
        }

        return static::getKernel()->getContainer();
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected static function getKernel()
    {
        if (null === static::$kernel) {
            static::$kernel = static::createKernel();
            static::$kernel->boot();
        }

        return static::$kernel;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * загружает фикстуры
     *
     * @param array $fixtures
     */
    protected static function loadFixtures($fixtures)
    {
        if (!is_array($fixtures)) {
            $fixtures = array($fixtures);
        }

        $loader = new Loader();

        foreach ($fixtures as $fixture) {
            if ($fixture instanceof ContainerAwareInterface) {
                $fixture->setContainer(self::getContainer());
            }
            $loader->addFixture($fixture);
        }

        $purger = new ORMPurger();
        $executor = new ORMExecutor(self::getContainer()->get('doctrine.orm.entity_manager'), $purger);
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * зпускает команду
     *
     * @param string $command
     *
     * @return string
     */
    protected function runCommand($command)
    {
        $application = new Application(static::getKernel());
        $application->setAutoExit(false);

        $fp = tmpfile();
        $input = new StringInput($command);
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        fseek($fp, 0);
        $output = '';
        while (!feof($fp)) {
            $output = fread($fp, 4096);
        }
        fclose($fp);

        return $output;
    }

    protected function refreshObjects()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_object($arg)) {
                $this->getEntityManager()->refresh($arg);
            } elseif (is_array($arg)) {
                call_user_func_array(array($this, 'refreshObjects'), $arg);
            }
        }

    }

    /**
     * рекурсивно удаляет объекты
     */
    protected static function deleteFixtures()
    {
        /** @var EntityManager $em */
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_object($arg)) {
                $obj = $em->find(get_class($arg), $arg->getId());
                if ($obj !== null) {
                    $em->remove($obj);
                }
            } elseif (is_array($arg)) {
                call_user_func_array(__NAMESPACE__ . '\TestCase::deleteFixtures', $arg);
            }
        }
        $em->flush();
    }

    /**
     * массив содержит только заданные ключи
     */
    public function assertArrayHasOnlyKeys(array $keys, array $array)
    {
        $size = sizeof($keys);
        $this->assertCount($size, $array);
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }


}
