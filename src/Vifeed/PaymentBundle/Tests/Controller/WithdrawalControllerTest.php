<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\PaymentBundle\Entity\Wallet;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class WithdrawalControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class WithdrawalControllerTest extends ApiTestCase
{

    /**
     * без авторизации
     */
    public function testPutWithdrawalUnauthorized()
    {
        $url = self::$router->generate('api_put_withdrawal');
        self::$client->request('PUT', $url, array());
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * попытка вывести на чужой кошелек
     */
    public function testPutWithdrawalNotOwnWallet()
    {
        $url = self::$router->generate('api_put_withdrawal');

        $data = [
              'withdrawal' => [
                    'wallet' => self::$parameters['fixtures']['wallets'][0]->getId(),
                    'amount' => 5000,
              ]
        ];

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode(), $response->getContent());
    }

    /**
     * ошибки формы
     *
     * @dataProvider putWithdrawalErrorsProvider
     */
    public function testPutWithdrawalErrors($data, $errors)
    {
        $url = self::$router->generate('api_put_withdrawal');

        $this->sendRequest(self::$testPublisher, 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, $errors);
    }

    /**
     * недостаточно денег
     */
    public function testPutWithdrawalNotEnoughMoney()
    {
        $url = self::$router->generate('api_put_withdrawal');

        $data = [
              'withdrawal' => [
                    'wallet' => self::$parameters['fixtures']['wallets'][0]->getId(),
                    'amount' => 100500,
              ]
        ];

        $this->sendRequest(self::$parameters['fixtures']['users'][0], 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['amount' => 'Недостаточно денег на балансе']);
    }

    /**
     * меньше минимальной суммы вывода
     */
    public function testPutWithdrawalSmallAmount()
    {
        $url = self::$router->generate('api_put_withdrawal');

        $data = [
              'withdrawal' => [
                    'wallet' => self::$parameters['fixtures']['wallets'][0]->getId(),
                    'amount' => 100,
              ]
        ];

        $this->sendRequest(self::$parameters['fixtures']['users'][0], 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, ['amount' => 'Минимальная сумма вывода - 1000 рублей']);
    }

    /**
     * Вывод денег
     */
    public function testPutWithdrawal()
    {
        $url = self::$router->generate('api_put_withdrawal');
        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();

        $data = [
              'withdrawal' => [
                    'wallet' => self::$parameters['fixtures']['wallets'][0]->getId(),
                    'amount' => 1000,
              ]
        ];

        $this->sendRequest(self::$parameters['fixtures']['users'][0], 'PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertArrayHasOnlyKeys(['status'], $content);
        $this->assertEquals('new', $content['status']);

        $mailCollector = self::$client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertRegExp('/Поступил запрос на вывод средств на сумму 1000 рублей от пользователя testpublisher1@vifeed.ru.<br\/>
<a href="http:\/\/' . $this->getContainer()->getParameter('backend.host') . '\/vifeed\/payment\/withdrawal\/list[^"]+">Перейти в админку<\/a>/',
                            $message->getBody());
        $this->assertEquals($this->getContainer()->getParameter('withdrawal.notification.email'), key($message->getTo()));
    }

    /**
     * @return array
     */
    public function putWithdrawalErrorsProvider()
    {
        $data = array(
              array(
                    array(),
                    array(
                          'wallet' => 'Значение не должно быть пустым.',
                          'amount' => 'Значение не должно быть пустым.',
                    )
              ),
              array(
                    array(
                          'withdrawal' => array(
                                'wallet' => '100500',
                                'amount' => '0',
                          )
                    ),
                    array(
                          'wallet' => 'Значение недопустимо.',
                          'amount' => 'Минимальная сумма вывода - 1000 рублей',
                    )
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
              ->setBalance(1000)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');
        $userManager->updateUser($publisher);

        $wallet1 = new Wallet();
        $wallet1->setUser($publisher)
                ->setNumber(12345)
                ->setType('yandex');
        self::$em->persist($wallet1);

        self::$em->flush();

        $tokenManager->createUserToken($publisher->getId());

        return ['users'   => [$publisher],
                'wallets' => [$wallet1]];
    }
}
 