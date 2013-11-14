<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;

/**
 * Class PaymentControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class PaymentControllerTest extends ApiTestCase
{

    public static function tearDownAfterClass()
    {

    }

    /**
     * @dataProvider putOrdersProvider
     */
    public function testPutOrder($data, $code, $errors = null)
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
        }
        if ($code == 201) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
            $order = $em->find('\Vifeed\PaymentBundle\Entity\Order', $content['id']);
            $this->assertNotNull($order);
            $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
            $this->assertTrue(bccomp($data['order']['amount'], $order->getPaymentInstruction()->getAmount()) === 0);
            $this->assertEquals(
                $data['jms_choose_payment_method']['method'],
                $order->getPaymentInstruction()->getPaymentSystemName()
            );
        }
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
                        'method' => 'my_payment_type'
                    )
                ),
                201,
            ),
        );

        return $data;
    }

}
 