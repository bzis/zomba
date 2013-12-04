<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\SystemBundle\Tests\ApiTestCase;

/**
 * Class PaymentControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class PaymentControllerTest extends ApiTestCase
{

    /**
     * тест формы создания заказа и её реакций
     *
     * @dataProvider putOrdersProvider
     */
    public function testPutOrderForm($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_order');
        self::$client->request('PUT', $url, $data);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('PUT', $url, $data);
        $this->assertEquals($code, self::$client->getResponse()->getStatusCode());

        $response = self::$client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        if ($errors !== null) {
            $this->assertArrayHasKey('errors', $content);
            $this->assertArrayHasKey('children', $content['errors']);
            $this->validateErros($content, $errors);
        } else {
            $this->assertArrayHasKey('order', $content);
            $this->assertArrayHasKey('id', $content['order']);
        }
    }

    /**
     * создание заказа
     *
     * @depends testPutOrderForm
     *
     * @return int
     */
    public function testPutOrder()
    {
        $amount = 61;

        $data = array(
            'order'                     => array(
                'amount' => $amount
            ),
            'jms_choose_payment_method' => array(
                'method' => 'robokassa'
            )
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest('PUT', $url, $data);

        $response = self::$client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $content['order']['id']);
        $this->assertNotNull($order);
        $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
        $this->assertEquals($amount, $data['order']['amount'], $order->getPaymentInstruction()->getAmount());
        $this->assertEquals(
            $data['jms_choose_payment_method']['method'],
            $order->getPaymentInstruction()->getPaymentSystemName()
        );

        return $order->getId();
    }

    /**
     * подготовка к оплате заказа
     *
     * @param int $id Order id
     *
     * @depends testPutOrder
     */
    public function testCompleteOrder($id)
    {
        $url = self::$router->generate('api_get_order_complete', array('order' => $id));
        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('GET', $url);
        $response = self::$client->getResponse();

        $this->assertEquals(303, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->assertTrue(is_array($content));
        $this->assertArrayHasKey('url', $content);

    }

    /**
     * тест обработки подтверждения от робокассы
     *
     * @param int $id Order id
     *
     * @depends testPutOrder
     */
    public function testRobokassaResultUrl($id)
    {
        /** @var Order $order */
        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $id);
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
        $this->assertEquals('OK' . $id, $content);

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $id);
        $user = $order->getUser();
        // здесь могут быть расхождения, потому что робокасса в тестовом режиме создаёт платёж с фиксированной суммой 1.79 WMZ
        $this->assertEquals($order->getAmount(), $order->getPaymentInstruction()->getApprovedAmount());
        $this->assertEquals($user->getBalance() - $balanceBefore, $order->getAmount());
    }


    /**
     * data-provider для testPutOrder
     *
     * @return array
     */
    public function putOrdersProvider()
    {
        $data = array(
            array(
                array(),
                400,
                array(
                    'amount' => 'Значение не должно быть пустым.',
                )
            ),
            array(
                array(
                    'order' => array(
                        'amount' => 0
                    ),
                ),
                400,
                array(
                    'amount' => 'Должно быть положительным числом',
                )
            ),
            array(
                array(
                    'order'                     => array(
                        'amount' => 100
                    ),
                    'jms_choose_payment_method' => array(
                        'method' => 'robokassa'
                    )
                ),
                201,
            ),
        );

        return $data;
    }

}
 