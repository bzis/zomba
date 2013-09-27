<?php

namespace Vifeed\CampaignBundle\Tests;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Vifeed\UserBundle\Entity\User;


class ApiTestCase extends TestCase
{

    /** @var User */
    protected static $user = null;

    /**
     * todo: генерить юзера в начале работы, а в конце его херить
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$user = self::createUser();
    }

    public static function tearDownAfterClass()
    {
        $um = static::createClient()->getContainer()->get('fos_user.user_manager');
        $user = $um->findUserByUsername('test');
        $um->deleteUser($user);

        parent::tearDownAfterClass();

    }

    /**
     * Создаёт тестового юзера
     *
     * @return User
     */
    protected static function createUser()
    {
        $userManager = self::$client->getContainer()->get('fos_user.user_manager');

        $user = $userManager->createUser()
              ->setEmail('test@test.test')
              ->setUsername('test')
              ->setPlainPassword('test');

        $userManager->updateUser($user);

        return $user;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return Crawler
     */
    protected function sendRequest($method, $url, $parameters = array())
    {
        $created = (new \DateTime())->format('Y-m-d H:i:s');
        $nonce = md5($created.rand());
        $digest = base64_encode(sha1(base64_decode($nonce) . $created . self::$user->getPassword(), true));

        $server = array(
            'HTTP_x-wsse' => 'UsernameToken Username="' . self::$user->getUsername() . '", ' .
            'PasswordDigest="' . $digest . '", ' .
            'Nonce="' . $nonce . '", Created="' . $created . '"'
        );
        return self::$client->request($method, $url, $parameters, array(), $server);
    }

}
