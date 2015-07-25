<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use JMS\Payment\CoreBundle\Entity\PaymentInstruction;
use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\User;

/**
 * Class PaymentControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class PaymentControllerTest extends ApiTestCase
{

    /**
     * Размещение заказа без авторизации
     */
    public function testPutOrderUnauthorized()
    {
        $url = self::$router->generate('api_put_order');
        self::$client->request('PUT', $url, []);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Размещение заказа без авторизации
     */
    public function testPutOrderWithPublisher()
    {
        $url = self::$router->generate('api_put_order');
        $this->sendRequest(self::$testPublisher, 'PUT', $url, []);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Статус заказа, которого нет
     */
    public function testGetOrderCompleteNotFound()
    {
        $url = self::$router->generate('api_get_order_complete', ['id' => -1]);
        self::$client->request('GET', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Статус заказа без авторизации
     */
    public function testGetOrderCompleteUnauthorized()
    {
        /** @var Order $order */
        $order = self::$parameters['fixtures']['orders'][0];
        $url = self::$router->generate('api_get_order_complete', ['id' => $order->getId()]);
        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Статус заказа другим юзером
     */
    public function testGetOrderCompleteNotOwner()
    {
        /** @var Order $order */
        $order = self::$parameters['fixtures']['orders'][0];
        $url = self::$router->generate('api_get_order_complete', ['id' => $order->getId()]);
        $this->sendRequest(self::$testPublisher, 'GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * тест формы создания заказа и её реакций
     *
     * @dataProvider putOrderErrorsProvider
     */
    public function testPutOrderErrors($data, $errors = null)
    {
        $url = self::$router->generate('api_put_order');

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);
        $this->assertEquals(400, self::$client->getResponse()->getStatusCode());

        $response = self::$client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertArrayHasKey('errors', $content);
        $this->assertArrayHasKey('children', $content['errors']);
        $this->validateErrors($content, $errors);
    }

    /**
     * создание заказа
     *
     * (почему-то тестовый сервер робокассы всегда отвечает, что принято к оплате 61 рубль)
     *
     * @return int
     */
    public function testPutRobokassaOrder()
    {
        $amount = 300000000;

        $data = array(
              'order'                     => array(
                    'amount' => $amount
              ),
              'jms_choose_payment_method' => array(
                    'method' => 'robokassa'
              )
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(303, $response->getStatusCode());

        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('url', $content);
        $this->assertArrayHasKey('orderId', $content);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $content['orderId']);
        $this->assertNotNull($order);
        $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
        $this->assertEquals($amount, $order->getPaymentInstruction()->getAmount());
        $this->assertEquals('robokassa', $order->getPaymentInstruction()->getPaymentSystemName());

        $this->assertEquals(Order::STATUS_PENDING, $order->getStatus());

        return $order->getId();
    }

    /**
     * @depends testPutRobokassaOrder
     */
    public function testRobokassaOrderComplete($orderId)
    {
        $url = self::$router->generate('api_get_order_complete', ['id' => $orderId]);
        $this->getContainer();
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(1, $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertEquals('paid', $content['status']);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $orderId);
        $this->assertEquals(Order::STATUS_PAID, $order->getStatus());
    }

    /**
     * создание заказа
     *
     * @return int
     */
    public function testPutPaypalOrder()
    {
        $amount = 100;

        $data = array(
              'order'                     => array(
                    'amount' => $amount
              ),
              'jms_choose_payment_method' => array(
                    'method' => 'paypal_express_checkout'
              )
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(303, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('url', $content);
        $this->assertArrayHasKey('orderId', $content);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $content['orderId']);
        $this->assertNotNull($order);
        $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
        $this->assertEquals($amount, $order->getPaymentInstruction()->getAmount());
        $this->assertEquals('paypal_express_checkout', $order->getPaymentInstruction()->getPaymentSystemName());

        $this->assertEquals(Order::STATUS_PENDING, $order->getStatus());
    }

    /**
     * создание заказа Qiwi
     *
     * @return int
     */
    public function testPutQiwiOrder()
    {
        $amount = 10;

        $data = [
              'order'                     => [
                    'amount' => $amount
              ],
              'jms_choose_payment_method' => [
                    'method'           => 'qiwi_wallet',
                    'data_qiwi_wallet' => [
                          'number' => '+79051111111'
                    ]

              ]
        ];

        $url = self::$router->generate('api_put_order');

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(303, $response->getStatusCode());

        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('url', $content);
        $this->assertArrayHasKey('orderId', $content);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $content['orderId']);
        $this->assertNotNull($order);
        $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
        $this->assertEquals($amount, $order->getPaymentInstruction()->getAmount());
        $this->assertEquals('qiwi_wallet', $order->getPaymentInstruction()->getPaymentSystemName());

        $this->assertEquals(Order::STATUS_PENDING, $order->getStatus());
    }

    /**
     * тест обработки подтверждения от робокассы
     */
    public function testRobokassaResultUrl()
    {
        /** @var Order $order */
        $order = self::$parameters['fixtures']['orders'][0];

        /** @var Order $order */
        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $order->getId());
        $user = $order->getUser();
        $balanceBefore = $user->getBalance();

        $data = array(
              'OutSum' => $order->getAmount(),
              'InvId'  => $order->getId(),
        );
        $data['SignatureValue'] = md5(
              join(':', $data) . ':' . $this->getContainer()->getParameter('karser_robokassa.password2')
        );
        $url = self::$router->generate('karser_robokassa_callback', $data);
        self::$client->request('GET', $url);
        $content = self::$client->getResponse()->getContent();
        $this->assertEquals('OK' . $order->getId(), $content);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $order->getId());
        $user = $order->getUser();
        // здесь могут быть расхождения, потому что робокасса в тестовом режиме создаёт платёж с фиксированной суммой 1.79 WMZ
        $this->assertEquals($order->getAmount(), $order->getPaymentInstruction()->getApprovedAmount());
        $this->assertEquals($user->getBalance() - $balanceBefore, $order->getAmount());
        $this->assertEquals(Order::STATUS_PAID, $order->getStatus());
    }


    /**
     * data-provider для testPutOrderErrors
     *
     * @return array
     */
    public function putOrderErrorsProvider()
    {
        $data = [
              [
                    [],
                    [
                          'amount' => 'Значение не должно быть пустым.',
                    ]
              ],
              [
                    [
                          'order' => [
                                'amount' => 0
                          ],
                    ],
                    [
                          'amount' => 'Должно быть положительным числом',
                    ]
              ],
              [
                    [
                          'order' => [
                                'amount' => 1000000000
                          ],
                    ],
                    [
                          'amount' => 'Не больше одного миллиарда за раз!',
                    ]
              ],
        ];

        return $data;
    }

    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = self::getContainer()->get('vifeed.user.wsse_token_manager');
        $ppc = self::getContainer()->get('payment.plugin_controller');

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

        $order = new Order();
        $order->setAmount(61)
              ->setUser($publisher);

        $instruction = new PaymentInstruction($order->getAmount(), 'RUR', 'robokassa');
        $ppc->createPaymentInstruction($instruction);

        $order->setPaymentInstruction($instruction);

        self::$em->persist($order);
        self::$em->flush();

        $payment = $ppc->createPayment(
                       $instruction->getId(),
                       $instruction->getAmount() - $instruction->getDepositedAmount()
        );
        $ppc->approveAndDeposit($payment->getId(), $payment->getTargetAmount());

        $tokenManager->createUserToken($publisher->getId());

        return ['orders' => [$order],
                'users'  => [$publisher]];
    }

}
 