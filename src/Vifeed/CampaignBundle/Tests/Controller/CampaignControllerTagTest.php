<?php


namespace Vifeed\CampaignBundle\Tests\Controller;


use Vifeed\CampaignBundle\Entity\Campaign;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class CampaignControllerTagTest
 *
 * @package Vifeed\CampaignBundle\Tests\Controller
 */
class CampaignControllerTagTest extends ApiTestCase
{

    /**
     * Сохранение тегов при создании кампании
     *
     * @return int
     */
    public function testCreateCampaignError()
    {
        $url = self::$router->generate('api_put_campaigns');

        $data = [
              'campaign' => [
                    'tags' => ['ааа', 'ббб ббб']
              ]
        ];

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->validateErrors($content, ['tags' => 'неправильный формат тегов']);
    }

    /**
     * Сохранение тегов при создании кампании
     *
     * @return int
     */
    public function testCreateCampaign()
    {
        $url = self::$router->generate('api_put_campaigns');

        $data = [
              'campaign' => [
                    'name'          => 'test1',
                    'hash'          => 'dafkjhasdfs',
                    'gender'        => 'male',
                    'bid'           => 10,
                    'generalBudget' => 100,
                    'dailyBudget'   => 0,
                    'tags'          => 'ааа, ббб ббб'
              ]
        ];

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ааа', 'ббб ббб'], $content['tags']);

        return $content['id'];
    }

    /**
     * Проверка, что теги сохранены
     *
     * @param int $campaignId
     *
     * @depends testCreateCampaign
     */
    public function testCreatedCampaignTags($campaignId)
    {
        $url = self::$router->generate('api_get_campaign', ['id' => $campaignId]);
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ааа', 'ббб ббб'], $content['tags']);
    }

    /**
     * Теги, заданные для кампании в фикстурах
     */
    public function testGetCampaignTags()
    {
        $advertiser = self::$parameters['fixtures']['advertiser'];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaign'];

        $url = self::$router->generate('api_get_campaign', ['id' => $campaign->getId()]);
        $this->sendRequest($advertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ааа', 'ббб'], $content['tags']);
    }

    /**
     * Сохранение тегов при изменении кампании
     *
     * @return int
     */
    public function testEditCampaign()
    {
        $advertiser = self::$parameters['fixtures']['advertiser'];
        /** @var Campaign $campaign */
        $campaign = self::$parameters['fixtures']['campaign'];

        $url = self::$router->generate('api_put_campaign', ['id' => $campaign->getId()]);
        $data = [
              'campaign' => [
                    'tags' => 'ббб, ггг еее'
              ]
        ];
        $this->sendRequest($advertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals('', $content);

        return $campaign->getId();
    }

    /**
     * Проверка, что теги сохранены
     *
     * @param int $campaignId
     *
     * @depends testEditCampaign
     */
    public function testEditedCampaignTags($campaignId)
    {
        $advertiser = self::$parameters['fixtures']['advertiser'];

        $url = self::$router->generate('api_get_campaign', ['id' => $campaignId]);
        $this->sendRequest($advertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertEquals(['ббб', 'ггг еее'], $content['tags']);
    }


    protected static function loadTestFixtures()
    {
        $campaignManager = self::getContainer()->get('vifeed.campaign.manager');
        $tagManager = self::getContainer()->get('vifeed.tag.manager');
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = static::getContainer()->get('vifeed.user.wsse_token_manager');

        $tag1 = $tagManager->loadOrCreateTag('ааа');
        $tag2 = $tagManager->loadOrCreateTag('ббб');

        /** @var User $advertiser */
        $advertiser = $userManager->createUser();
        $advertiser
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');
        $userManager->updateUser($advertiser);

        $campaign1 = new Campaign();
        $campaign1
              ->setStatus(Campaign::STATUS_ARCHIVED)
              ->setBid(3)
              ->setName('111')
              ->setUser($advertiser)
              ->setHash(substr(md5(mt_rand(1, 100)), 0, 11))
              ->setDailyBudget(7)
              ->setGeneralBudget(10);
        $campaignManager->save($campaign1);

        $tagManager->addTags([$tag1, $tag2], $campaign1);
        $tagManager->saveTagging($campaign1);

        $tokenManager->createUserToken($advertiser->getId());

        return [
              'advertiser' => $advertiser,
              'campaign'   => $campaign1
        ];
    }


}
 