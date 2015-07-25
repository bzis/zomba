<?php

namespace Vifeed\SystemBundle\Tests;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Vifeed\UserBundle\Entity\User;


class ApiTestCase extends TestCase
{
    /** @var User */
    protected static $testAdvertiser;
    /** @var User */
    protected static $testPublisher;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$parameters['fixtures'] = static::loadTestFixtures();

        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');
        $userManager = self::getContainer()->get('fos_user.user_manager');

        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser@vifeed.ru')
              ->setUsername('testadvertiser@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($advertiser);

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher@vifeed.ru')
              ->setUsername('testpublisher@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $userManager->updateUser($publisher);

        $tokenManager->createUserToken($advertiser->getId());
        $tokenManager->createUserToken($publisher->getId());

        self::$testAdvertiser = $advertiser;
        self::$testPublisher = $publisher;
    }

    public static function tearDownAfterClass()
    {
        self::deleteFixtures(static::$parameters['fixtures'], self::$testAdvertiser, self::$testPublisher);

        parent::tearDownAfterClass();
    }


    /**
     * Создаёт тестового юзера
     *
     * @return User
     */
    protected function createUser()
    {
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

        return $user;
    }

    /**
     * @param User   $user
     * @param string $method
     * @param string $url
     * @param array  $parameters
     *
     * @return Crawler
     */
    protected function sendRequest(User $user, $method, $url, $parameters = array())
    {
        $tokenManager = static::$container->get('vifeed.user.wsse_token_manager');
        $token = $tokenManager->getUserToken($user->getId());

        $created = (new \DateTime())->format('Y-m-d H:i:s');
        $nonce = md5($created . rand());
        $digest = base64_encode(sha1(base64_decode($nonce) . $created . $token, true));

        $server = array(
              'HTTP_x-wsse' => 'UsernameToken Username="' . $user->getUsername() . '", ' .
                    'PasswordDigest="' . $digest . '", ' .
                    'Nonce="' . $nonce . '", Created="' . $created . '"'
        );

        return self::$client->request($method, $url, $parameters, array(), $server);
    }

    /**
     * @param array $content
     * @param array $errors
     */
    protected function validateErrors($content, $errors)
    {
        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('children', $content['errors']);

        foreach ($errors as $field => $error) {
            // error общий на форму
            if (is_numeric($field)) {
                $this->assertArrayHasKey('errors', $content['errors']);
                $this->assertContains($error, $content['errors']['errors']);
            } else {
                $this->assertArrayHasKey($field, $content['errors']['children']);

                if (!is_array($error)) {
                    $this->assertArrayHasKey('errors', $content['errors']['children'][$field]);
                    $this->assertContains($error, $content['errors']['children'][$field]['errors']);

                    // сложные поля типа "пароль два раза"
                } else {
                    array_walk_recursive(
                          $errors,
                          function ($item, $key) use ($field, $content) {
                              $this->assertArrayHasKey($key, $content['errors']['children'][$field]['children']);
                              $this->assertArrayHasKey(
                                   'errors', $content['errors']['children'][$field]['children'][$key]
                              );
                              $this->assertContains(
                                   $item, $content['errors']['children'][$field]['children'][$key]['errors']
                              );
                          }
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    protected static function loadTestFixtures()
    {
        return [];
    }

}
