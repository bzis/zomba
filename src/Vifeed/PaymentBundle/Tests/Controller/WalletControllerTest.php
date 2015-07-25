<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\PaymentBundle\Entity\Wallet;
use Vifeed\PaymentBundle\Entity\Withdrawal;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class WalletControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class WalletControllerTest extends ApiTestCase
{

    /**
     * попытка добавить кошелек без авторизации
     */
    public function testPutWalletUnauthorized()
    {
        $url = self::$router->generate('api_put_wallet');
        self::$client->request('PUT', $url, []);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Новый кошелёк
     *
     * @dataProvider putWalletsProvider
     */
    public function testPutWallets($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_wallet');

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals($code, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        if ($errors !== null) {
            $this->assertArrayHasKey('errors', $content);
            $this->assertArrayHasKey('children', $content['errors']);
            $this->validateErrors($content, $errors);
        } else {
            $this->assertArrayHasKey('id', $content);
        }
    }

    /**
     * попытка получить кошельки без авторизации
     */
    public function testGetWalletsUnauthorized()
    {
        $url = self::$router->generate('api_get_wallets');

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Список кошельков по юзеру
     */
    public function testGetWallets()
    {
        $user = self::$parameters['fixtures']['users'][0];

        $url = self::$router->generate('api_get_wallets');

        $this->sendRequest($user, 'GET', $url);
        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $data);
        $this->assertCount(2, $data);
        $this->assertCount(5, $data[0]);

        $this->assertArrayHasKey('id', $data[0]);
        $this->assertEquals('wm', $data[1]['type']);
        $this->assertEquals('12345', $data[1]['number']);
        $this->assertEquals(0, $data[1]['withdrawnAmount']);
        $this->assertEquals(null, $data[1]['lastOperationDate']);

        $this->assertEquals('yandex', $data[0]['type']);
        $this->assertEquals('12345', $data[0]['number']);
        $this->assertEquals(100, $data[0]['withdrawnAmount']);
        $this->assertEquals('2014-02-12 15:00:00', $data[0]['lastOperationDate']);
    }

    /**
     * попытка удаления кошелька без авторизации
     */
    public function testDeleteWalletUnauthorized()
    {
        /** @var Wallet $wallet */
        $wallet = self::$parameters['fixtures']['wallets'][2];
        $url = self::$router->generate('api_delete_wallet', ['id' => $wallet->getId()]);
        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка удаления чужого кошелька
     */
    public function testDeleteWalletNotOwner()
    {
        /** @var Wallet $wallet */
        $wallet = self::$parameters['fixtures']['wallets'][2];
        $url = self::$router->generate('api_delete_wallet', ['id' => $wallet->getId()]);
        $this->sendRequest(self::$testPublisher, 'DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка удаления несуществующего кошелька
     */
    public function testDeleteWalletNotExists()
    {
        $url = self::$router->generate('api_delete_wallet', ['id' => -1]);
        $this->sendRequest(self::$testPublisher, 'DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Удаление кошелька
     */
    public function testDeleteWallet()
    {
        /** @var Wallet $wallet */
        $wallet = self::$parameters['fixtures']['wallets'][2];
        $user = self::$parameters['fixtures']['users'][1];

        $url = self::$router->generate('api_delete_wallet', ['id' => $wallet->getId()]);
        $this->sendRequest($user, 'DELETE', $url);
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        $wallet = $this->getEntityManager()->getRepository('VifeedPaymentBundle:Wallet')->find($wallet->getId());
        $this->assertNull($wallet);
    }

    /**
     * Типы кошельков
     */
    public function testGetWalletTypes()
    {
        $url = self::$router->generate('api_get_wallet_types');
        $this->sendRequest(self::$testPublisher, 'GET', $url);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $data);
        $this->assertCount(3, $data);
        $this->assertArrayHasKey('yandex', $data);
        $this->assertEquals('Яндекс.Деньги', $data['yandex']);
        $this->assertArrayHasKey('wm', $data);
        $this->assertEquals('WebMoney', $data['wm']);
        $this->assertArrayHasKey('qiwi', $data);
        $this->assertEquals('Qiwi', $data['qiwi']);

    }

    /**
     * @return array
     */
    public function putWalletsProvider()
    {
        $data = array(
              array(
                    array(),
                    400,
                    array(
                          'type'   => 'Значение не должно быть пустым.',
                          'number' => 'Значение не должно быть пустым.',
                    )
              ),
              array(
                    array(
                          'wallet' => array(
                                'type'   => 'yandex',
                                'number' => '12345',
                          )
                    ),
                    201,
              ),
        );

        return $data;
    }

    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = self::getContainer()->get('vifeed.user.wsse_token_manager');

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

        $publisher2 = $userManager->createUser();
        $publisher2
              ->setEmail('testpublisher2@vifeed.ru')
              ->setUsername('testpublisher2@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');
        $userManager->updateUser($publisher2);

        $wallet1 = new Wallet();
        $wallet1->setUser($publisher)
                ->setNumber(12345)
                ->setType('yandex');
        self::$em->persist($wallet1);

        $wallet2 = new Wallet();
        $wallet2->setUser($publisher)
                ->setNumber(12345)
                ->setType('wm');
        self::$em->persist($wallet2);

        $wallet3 = new Wallet();
        $wallet3->setUser($publisher2)
                ->setNumber(12345)
                ->setType('wm');
        self::$em->persist($wallet3);

        $withdrawal1 = new Withdrawal();
        $withdrawal1->setUser($publisher)
                    ->setAmount(40)
                    ->setWallet($wallet1)
                    ->setCreatedAt(new \DateTime('2014-02-09'))
                    ->setStatus(Withdrawal::STATUS_OK);
        self::$em->persist($withdrawal1);

        $withdrawal2 = new Withdrawal();
        $withdrawal2->setUser($publisher)
                    ->setAmount(60)
                    ->setWallet($wallet1)
                    ->setCreatedAt(new \DateTime('2014-02-10'))
                    ->setStatus(Withdrawal::STATUS_OK);
        self::$em->persist($withdrawal2);

        $withdrawal3 = new Withdrawal();
        $withdrawal3->setUser($publisher)
                    ->setAmount(60)
                    ->setWallet($wallet1)
                    ->setCreatedAt(new \DateTime('2014-02-10'))
                    ->setStatus(Withdrawal::STATUS_CREATED);
        self::$em->persist($withdrawal3);

        $withdrawal4 = new Withdrawal();
        $withdrawal4->setUser($publisher)
                    ->setAmount(60)
                    ->setWallet($wallet2)
                    ->setCreatedAt(new \DateTime('2014-02-10'))
                    ->setStatus(Withdrawal::STATUS_ERROR);
        self::$em->persist($withdrawal4);

        self::$em->flush();
        self::$em->getConnection()->executeQuery('update withdrawal set updated_at="2014-02-12 15:00" where id=' . $withdrawal1->getId());
        self::$em->getConnection()->executeQuery('update withdrawal set updated_at="2014-02-11 11:00" where id=' . $withdrawal2->getId());

        $tokenManager->createUserToken($publisher->getId());
        $tokenManager->createUserToken($publisher2->getId());

        return ['users'   => [$publisher, $publisher2],
                'wallets' => [$wallet1, $wallet2, $wallet3]];
    }

}
 