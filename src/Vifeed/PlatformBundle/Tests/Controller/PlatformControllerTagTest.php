<?php

namespace Vifeed\PlatformBundle\Tests\Controller;


use Doctrine\ORM\EntityManager;
use Vifeed\PlatformBundle\Entity\Platform;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

class PlatformControllerTagTest extends ApiTestCase
{

    /**
     * Сохранение тегов при создании площадки
     *
     * @return int
     */
    public function testCreatePlatform()
    {
        $url = self::$router->generate('api_put_platforms');

        $data = [
              'platform' => [
                    'name'        => 'test1',
                    'description' => 'aa',
                    'url'         => 'ok.ru',
                    'tags'        => 'ааа, ббб ббб',
                    'type'        => 'site'
              ]
        ];

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ааа', 'ббб ббб'], $content['tags']);

        return $content['id'];
    }

    /**
     * Проверка, что теги сохранены
     *
     * @param int $platformId
     *
     * @depends testCreatePlatform
     */
    public function testCreatedPlatformTags($platformId)
    {
        $url = self::$router->generate('api_get_platform', ['id' => $platformId]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ааа', 'ббб ббб'], $content['tags']);
    }

    /**
     * Теги, заданные для площадки в фикстурах
     */
    public function testGetPlatformTags()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platform'];

        $url = self::$router->generate('api_get_platform', ['id' => $platform->getId()]);
        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ааа', 'ббб'], $content['tags']);
    }

    /**
     * Сохранение тегов при изменении площадки
     *
     * @return int
     */
    public function testEditPlatform()
    {
        $publisher = self::$parameters['fixtures']['publisher'];
        /** @var Platform $platform */
        $platform = self::$parameters['fixtures']['platform'];

        $url = self::$router->generate('api_put_platform', ['id' => $platform->getId()]);
        $data = [
              'platform' => [
                    'tags' => 'Ббб, ггг еее'
              ]
        ];
        $this->sendRequest($publisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertEquals('', $content);

        return $platform->getId();
    }

    /**
     * Проверка, что теги сохранены
     *
     * @param int $platformId
     *
     * @depends testEditPlatform
     */
    public function testEditedPlatformTags($platformId)
    {
        $publisher = self::$parameters['fixtures']['publisher'];

        $url = self::$router->generate('api_get_platform', ['id' => $platformId]);
        $this->sendRequest($publisher, 'GET', $url);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ббб', 'ггг еее'], $content['tags']);
    }


    protected static function loadTestFixtures()
    {
        /** @var EntityManager $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        $tagManager = self::getContainer()->get('vifeed.tag.manager');
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');

        $tag1 = $tagManager->loadOrCreateTag('Ааа');
        $tag2 = $tagManager->loadOrCreateTag('ббб');

        /** @var User $publisher */
        $publisher = $userManager->createUser();
        $publisher
              ->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');
        $userManager->updateUser($publisher);

        $platform = new Platform();
        $platform->setUser($publisher)
                 ->setName('name1')
                 ->setDescription('111')
                 ->setUrl('url1');
        $entityManager->persist($platform);
        $entityManager->flush();

        $tagManager->addTags([$tag1, $tag2], $platform);
        $tagManager->saveTagging($platform);

        $tokenManager->createUserToken($publisher->getId());

        return [
              'publisher' => $publisher,
              'platform'  => $platform
        ];
    }


}
 