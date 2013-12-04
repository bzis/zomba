<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\PaymentBundle\Entity\Wallet;
use Vifeed\SystemBundle\Tests\ApiTestCase;

/**
 * Class WithdrawalControllerTest
 *
 * @package Vifeed\PaymentBundle\Tests\Controller
 */
class WithdrawalControllerTest extends ApiTestCase
{

    /**
     * Вывод денег
     *
     * @//dataProvider putWithdrawalProvider
     */
    public function testPutWithdrawal()
    {
        $url = self::$router->generate('api_put_withdrawal');
        self::$client->request('PUT', $url, array());
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());

        foreach ($this->putWithdrawalProvider() as $set) {
            list ($data, $code) = $set;
            $errors = isset($set[2]) ? $set[2] : null;

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
                $this->assertArrayHasKey('withdrawal', $content);
                $this->assertArrayHasKey('status', $content['withdrawal']);
            }
        }

    }

    /**
     * @return array
     */
    public function putWithdrawalProvider()
    {
        $data = array(
            array(
                array(),
                400,
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
                400,
                array(
                    'wallet' => 'Значение недопустимо.',
                    'amount' => 'Должно быть положительным числом',
                )
            ),
            array(
                array(
                    'withdrawal' => array(
                        'wallet' => $this->createWallet(),
                        'amount' => 12345,
                    )
                ),
                400,
                array(
                    'amount' => 'Недостаточно денег на балансе',
                )
            ),
            array(
                array(
                    'withdrawal' => array(
                        'wallet' => $this->createWallet(),
                        'amount' => 50,
                    )
                ),
                201,
            ),
        );

        return $data;
    }

    protected function createWallet()
    {
        $wallet = new Wallet();
        $wallet
              ->setUser(static::createUser())
              ->setType('yandex')
              ->setNumber('123456');

        $em = $this->getEntityManager();
        $em->persist($wallet);
        $em->flush();

        return $wallet->getId();
    }
}
 