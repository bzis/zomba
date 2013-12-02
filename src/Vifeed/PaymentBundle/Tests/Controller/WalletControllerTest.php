<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\PaymentBundle\Entity\Wallet;
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
     * Новый кошелёк
     *
     * @dataProvider putWalletsProvider
     */
    public function testPutWallets($data, $code, $errors = null)
    {
        $url = self::$router->generate('api_put_wallet');
        self::$client->request('PUT', $url, $data);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals($code, $response->getStatusCode(), $response->getContent());
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        if ($errors !== null) {
            $this->assertArrayHasKey('errors', $content);
            $this->assertArrayHasKey('children', $content['errors']);
            $this->validateErros($content, $errors);
        } else {
            $this->assertArrayHasKey('wallet', $content);
            $this->assertArrayHasKey('id', $content['wallet']);
        }
    }

    /**
     * Список кошельков по юзеру
     */
    public function testGetWallets()
    {
        $url = self::$router->generate('api_get_wallets');

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('GET', $url);
        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('wallets', $data);
        $this->assertEquals(1, sizeof($data['wallets']));
        $this->assertArrayHasKey('id', $data['wallets'][0]);
        $this->assertEquals('yandex', $data['wallets'][0]['type']);
        $this->assertEquals('12345', $data['wallets'][0]['number']);

        return $data['wallets'][0]['id'];
    }

    /**
     * Удаление кошелька
     *
     * @depends testGetWallets
     */
    public function testDeleteWallet($id)
    {
        $url = self::$router->generate('api_delete_wallet', array('id' => -1));

        self::$client->request('DELETE', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        $this->sendRequest('DELETE', $url);
        $this->assertEquals(404, self::$client->getResponse()->getStatusCode());

        $wallet = $this->getEntityManager()->getRepository('VifeedPaymentBundle:Wallet')->find($id);
        $this->assertNotNull($wallet);

        $url = self::$router->generate('api_delete_wallet', array('id' => $id));
        $this->sendRequest('DELETE', $url);
        $this->assertEquals(204, self::$client->getResponse()->getStatusCode());

        $wallet = $this->getEntityManager()->getRepository('VifeedPaymentBundle:Wallet')->find($id);
        $this->assertNull($wallet);
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

}
 