<?php
namespace Vifeed\PaymentBundle\Tests\Controller;

use Vifeed\PaymentBundle\Entity\Order;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\Company;
use Vifeed\UserBundle\Entity\User;

class BankTransferPaymentTest extends ApiTestCase
{
    /**
     * создание заказа
     *
     * @return int
     */
    public function testPutBankTransferOrderErr1()
    {
        $user = self::$parameters['fixtures']['users'][0];

        $amount = 1000;

        $data = array(
              'order'                     => [
                    'amount' => $amount
              ],
              'jms_choose_payment_method' => [
                    'method' => 'bank_transfer',
              ]
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest($user, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Вы не ввели реквизиты компании', $content['message']);
    }

    /**
     * создание заказа
     *
     * @return int
     */
    public function testPutBankTransferOrderErr2()
    {
        $user = self::$parameters['fixtures']['users'][1];

        $amount = 1000;

        $data = array(
              'order'                     => [
                    'amount' => $amount
              ],
              'jms_choose_payment_method' => [
                    'method' => 'bank_transfer',
              ]
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest($user, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Ваша компания проверяется', $content['message']);
    }

    /**
     * создание заказа
     *
     * @return int
     */
    public function testPutBankTransferOrderOk()
    {
        $user = self::$parameters['fixtures']['users'][2];

        $amount = 1000;

        $data = array(
              'order'                     => [
                    'amount' => $amount
              ],
              'jms_choose_payment_method' => [
                    'method' => 'bank_transfer',
              ]
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest($user, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(303, $response->getStatusCode());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['url'], $content);

        $url = $content['url'];
        $router = $this->getContainer()->get('router');
        $router->getContext()->setMethod('GET');
        $matched = $router->match($url);

        $this->assertInternalType('array', $matched);
        $this->assertArrayHasKey('_route', $matched);
        $this->assertEquals('order_bill', $matched['_route']);

        $this->assertArrayHasKey('id', $matched);

        return $matched['id'];
    }

    /**
     * @depends testPutBankTransferOrderOk
     */
    public function testBankTransferBillUnauthorized($orderId)
    {
        $url = self::$router->generate('order_bill', ['id' => $orderId]);
        self::$client->request('GET', $url);

        $response = self::$client->getResponse();

        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @depends testPutBankTransferOrderOk
     */
    public function testBankTransferBillNotOwner($orderId)
    {
        $url = self::$router->generate('sign_in');
        $data = [
              '_username' => 'testpublisher@vifeed.ru',
              '_password' => '12345',
        ];
        self::$client->request('POST', $url, $data);

        $url = self::$router->generate('order_bill', ['id' => $orderId]);
        self::$client->request('GET', $url);

        $response = self::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @depends testPutBankTransferOrderOk
     */
    public function testBankTransferBillOk($orderId)
    {
        $url = self::$router->generate('sign_in');
        $data = [
              '_username' => 'testpublisher3@vifeed.ru',
              '_password' => '12345',
        ];
        self::$client->request('POST', $url, $data);

        $url = self::$router->generate('order_bill', ['id' => $orderId]);
        self::$client->request('GET', $url);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

//        $f = fopen(__DIR__.'/receipt.pdf', 'w');
//        fwrite($f, $response->getContent());
//        fclose($f);
        $disposition = $response->headers->get('content-disposition');
        preg_match('/Bill-(\d+)\.pdf/', $disposition, $matches);
        $orderId = $matches[1];

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $orderId);
        $this->assertNotNull($order);
        $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
        $this->assertEquals('bank_transfer', $order->getPaymentInstruction()->getPaymentSystemName());

        $this->assertEquals(Order::STATUS_PENDING, $order->getStatus());
    }

    /**
     * создание заказа
     *
     * @return int
     */
    public function testPutBankReceiptOrder()
    {
        $amount = 1000;

        $data = array(
              'order'                     => array(
                    'amount' => $amount
              ),
              'jms_choose_payment_method' => array(
                    'method' => 'bank_receipt'
              )
        );

        $url = self::$router->generate('api_put_order');

        $this->sendRequest(self::$testAdvertiser, 'PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(303, $response->getStatusCode());
        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $this->assertArrayHasOnlyKeys(['url'], $content);

        $url = $content['url'];
        $router = $this->getContainer()->get('router');
        $router->getContext()->setMethod('GET');
        $matched = $router->match($url);

        $this->assertInternalType('array', $matched);
        $this->assertArrayHasKey('_route', $matched);
        $this->assertEquals('order_bill', $matched['_route']);

        $this->assertArrayHasKey('id', $matched);

        return $matched['id'];
    }

    /**
     * @depends testPutBankReceiptOrder
     */
    public function testBankReceiptBill($orderId)
    {
        $url = self::$router->generate('sign_in');
        $data = [
              '_username' => 'testadvertiser@vifeed.ru',
              '_password' => '12345',
        ];
        self::$client->request('POST', $url, $data);

        $url = self::$router->generate('order_bill', ['id' => $orderId]);
        self::$client->request('GET', $url);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

//        $f = fopen(__DIR__.'/receipt.pdf', 'w');
//        fwrite($f, $response->getContent());
//        fclose($f);
        $disposition = $response->headers->get('content-disposition');
        preg_match('/Bill-(\d+)\.pdf/', $disposition, $matches);
        $orderId = $matches[1];

        $order = $this->getEntityManager()->find('\Vifeed\PaymentBundle\Entity\Order', $orderId);
        $this->assertNotNull($order);
        $this->assertInstanceOf('\Vifeed\PaymentBundle\Entity\Order', $order);
        $this->assertEquals('bank_receipt', $order->getPaymentInstruction()->getPaymentSystemName());

        $this->assertEquals(Order::STATUS_PENDING, $order->getStatus());
    }

    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = self::getContainer()->get('vifeed.user.wsse_token_manager');

        /** @var User $user1 */
        $user1 = $userManager->createUser();
        $user1->setEmail('testpublisher1@vifeed.ru')
              ->setUsername('testpublisher1@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');
        $userManager->updateUser($user1);

        $company1 = new Company();
        $company1->setSystem('УСН')
                 ->setName('ООО компания')
                 ->setAddress('12345')
                 ->setBankAccount('12345')
                 ->setBic('12345')
                 ->setContactName('12345')
                 ->setCorrespondentAccount('12345')
                 ->setInn('12345')
                 ->setKpp('12345')
                 ->setPosition('12345')
                 ->setPhone('12345')
                 ->setIsApproved(false);

        $company2 = new Company();
        $company2->setSystem('УСН')
                 ->setName('ООО компания2')
                 ->setAddress('12345')
                 ->setBankAccount('12345')
                 ->setBic('12345')
                 ->setContactName('12345')
                 ->setCorrespondentAccount('12345')
                 ->setInn('12345')
                 ->setKpp('12345')
                 ->setPosition('12345')
                 ->setPhone('12345')
                 ->setIsApproved(true);

        /** @var User $user2 */
        $user2 = $userManager->createUser();
        $user2->setEmail('testpublisher2@vifeed.ru')
              ->setUsername('testpublisher2@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345')
              ->setCompany($company1);
        self::$em->persist($company1);
        $userManager->updateUser($user2);

        /** @var User $user3 */
        $user3 = $userManager->createUser();
        $user3->setEmail('testpublisher3@vifeed.ru')
              ->setUsername('testpublisher3@vifeed.ru')
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345')
              ->setCompany($company2);

        self::$em->persist($company2);
        $userManager->updateUser($user3);

        self::$em->flush();

        $tokenManager->createUserToken($user1->getId());
        $tokenManager->createUserToken($user2->getId());
        $tokenManager->createUserToken($user3->getId());

        return ['users' => [$user1, $user2, $user3]];
    }
}
 