<?php

namespace Vifeed\FrontendBundle\Tests\Controller;

use Vifeed\SystemBundle\Tests\ApiTestCase;

class PartnershipControllerTest extends ApiTestCase
{
    /**
     * заявка на партнёрство отправлена
     */
    public function testPartnershipOk()
    {
        $url = self::$router->generate('api_put_partnership');

        $data = [
              'partnership' => [
                    'name'  => 'Иван Иванович',
                    'email' => 'test@vifeed.co',
                    'phone' => '1234567890'
              ]
        ];

        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();
        self::$client->request('PUT', $url, $data);
        $response = self::$client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());

        $mailCollector = self::$client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals([$this->getContainer()->getParameter('partnership.notification.email') => null], $message->getTo());
        $this->assertEquals(['test@vifeed.co' => null], $message->getReplyTo());
        $this->assertContains('Иван Иванович', $message->getBody());
        $this->assertContains('test@vifeed.co', $message->getBody());
        $this->assertContains('1234567890', $message->getBody());
    }

    /**
     * ошибки формы заявки на партнёрство
     *
     * @dataProvider partnershipFormErrorsProvider
     */
    public function testPartnershipFormErrors($data, $errors)
    {
        $url = self::$router->generate('api_put_partnership');

        self::$client->request('PUT', $url, $data);
        $response = self::$client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), JSON_UNESCAPED_UNICODE);
        $this->validateErrors($content, $errors);
    }

    /**
     * data-provider для testPartnershipFormErrors
     */
    public function partnershipFormErrorsProvider()
    {
        return [
              [
                    [],
                    [
                          'name'  => 'Значение не должно быть пустым.',
                          'phone' => 'Значение не должно быть пустым.',
                          'email' => 'Значение не должно быть пустым.'
                    ]
              ],
              [
                    ['partnership' => [
                          'name'  => 'имя',
                          'phone' => '12356',
                          'email' => 'email'
                    ]],
                    [
                          'name'  => 'Значение слишком короткое. Должно быть равно 5 символам или больше.',
                          'phone' => 'Значение слишком короткое. Должно быть равно 9 символам или больше.',
                          'email' => 'Значение адреса электронной почты недопустимо.'
                    ]
              ]
        ];
    }
}
