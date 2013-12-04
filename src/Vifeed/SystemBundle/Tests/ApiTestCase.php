<?php

namespace Vifeed\SystemBundle\Tests;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Vifeed\UserBundle\Entity\User;


class ApiTestCase extends TestCase
{

    /** @var User */
    protected static $user = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$user = self::createUser();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        $um = static::getContainer()->get('fos_user.user_manager');
        $user = $um->findUserByUsername('test');
        $um->deleteUser($user);
        static::$user = null;
    }

    /**
     * Создаёт тестового юзера
     *
     * @return User
     */
    protected static function createUser()
    {
        if (static::$user !== null) {
            return static::$user;
        }

        $userManager = static::getContainer()->get('fos_user.user_manager');
        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');

        $user = $userManager->createUser()
              ->setEmail('test@test.test')
              ->setUsername('test')
              ->setPlainPassword('test')
              ->setBalance(100)
              ->setEnabled(true);

        $userManager->updateUser($user);
        $tokenManager->createUserToken($user->getId());

        static::$user = $user;

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

        $tokenManager = static::$container->get('vifeed.user.wsse_token_manager');
        $token = $tokenManager->getUserToken(self::$user->getId());

        $created = (new \DateTime())->format('Y-m-d H:i:s');
        $nonce = md5($created . rand());
        $digest = base64_encode(sha1(base64_decode($nonce) . $created . $token, true));

        $server = array(
            'HTTP_x-wsse' => 'UsernameToken Username="' . self::$user->getUsername() . '", ' .
                  'PasswordDigest="' . $digest . '", ' .
                  'Nonce="' . $nonce . '", Created="' . $created . '"'
        );

        return self::$client->request($method, $url, $parameters, array(), $server);
    }

    /**
     * @param array $content
     * @param array $errors
     */
    protected function validateErros($content, $errors)
    {
        foreach ($errors as $field => $error) {
            $this->assertArrayHasKey($field, $content['errors']['children']);
            if (!is_array($error)) {
                $this->assertArrayHasKey('errors', $content['errors']['children'][$field]);
                $this->assertTrue(in_array($error, $content['errors']['children'][$field]['errors']));
            } else {
                array_walk_recursive(
                    $errors,
                    function ($item, $key) use ($field, $content) {
                        $this->assertArrayHasKey($key, $content['errors']['children'][$field]['children']);
                        $this->assertArrayHasKey(
                            'errors',
                            $content['errors']['children'][$field]['children'][$key]
                        );
                        $this->assertTrue(
                            in_array($item, $content['errors']['children'][$field]['children'][$key]['errors'])
                        );
                    }
                );
            }
        }
    }

}
