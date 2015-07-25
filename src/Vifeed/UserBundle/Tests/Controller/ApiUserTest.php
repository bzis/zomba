<?php

namespace Vifeed\UserBundle\Tests\Controller;

use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Vifeed\SystemBundle\Tests\ApiTestCase;
use Vifeed\UserBundle\Entity\Company;
use Vifeed\UserBundle\Entity\User;

class ApiUserTest extends ApiTestCase
{

    public function testGetUserUnauthorized()
    {
        $url = self::$router->generate('api_get_user');

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * User по id
     */
    public function testGetUserOk()
    {
        $url = self::$router->generate('api_get_user');

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $data);

        $keys = ['id', 'balance', 'email', 'last_login', 'type', 'first_name', 'surname', 'phone', 'notification'];
        $this->assertArrayHasOnlyKeys($keys, $data);

        $this->assertEquals('advertiser', $data['type']);
        $this->assertEquals(['news' => 1], $data['notification']);
        $this->assertEquals(self::$testAdvertiser->getId(), $data['id']);
    }

    /**
     * @param array $data
     * @param array $errors
     *
     * @dataProvider patchUserErrorsProvider
     */
    public function testPatchUserProfileErrors($data, $errors)
    {
        $user = self::$parameters['fixtures']['users'][0];

        $url = self::$router->generate('api_patch_user');

        $this->sendRequest($user, 'PATCH', $url, $data);
        $this->assertEquals(400, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->validateErrors($content, $errors);
    }

    /**
     * изменение данных пользователя
     */
    public function testPatchUserProfileOk1()
    {
        $user = self::$parameters['fixtures']['users'][0];

        $url = self::$router->generate('api_patch_user');

        $data = ['profile' => [
              'first_name' => 'ccc'
        ]];

        $this->sendRequest($user, 'PATCH', $url, $data);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertEquals('', $content);

        return $user;
    }

    /**
     * @depends testPatchUserProfileOk1
     */
    public function testPatchedUserProfileOk1(User $user)
    {
        $url = self::$router->generate('api_get_user');

        $this->sendRequest($user, 'GET', $url);

        $content = self::$client->getResponse()->getContent();

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertEquals('ccc', $content['first_name']);
        $this->assertEquals('bbb', $content['surname']);
        $this->assertEquals('123', $content['phone']);
    }

    /**
     * изменение данных пользователя
     */
    public function testPatchUserProfileOk2()
    {
        $user = self::$parameters['fixtures']['users'][1];

        $url = self::$router->generate('api_patch_user');

        $data = ['profile' => [
              'first_name'   => 'ccc',
              'surname'      => 'ddd',
              'phone'        => '3456789',
              'notification' => ['email' => 0, 'sms' => 1]
        ]];

        $this->sendRequest($user, 'PATCH', $url, $data);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        return $user;
    }

    /**
     * @depends testPatchUserProfileOk2
     */
    public function testPatchedUserProfileOk2(User $user)
    {
        $url = self::$router->generate('api_get_user');

        $this->sendRequest($user, 'GET', $url);

        $content = self::$client->getResponse()->getContent();

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertEquals('ccc', $content['first_name']);
        $this->assertEquals('ddd', $content['surname']);
        $this->assertEquals('3456789', $content['phone']);
        $this->assertEquals(1, $content['notification']['sms']);
        $this->assertEquals(0, $content['notification']['email']);
    }

    /**
     * изменение емейла пользователя
     */
    public function testPatchUserEmailOk()
    {
        $user = self::$parameters['fixtures']['users'][2];

        $url = self::$router->generate('api_patch_user');

        $data = ['profile' => [
              'email' => 'testadvertiser100@vifeed.ru'
        ]];

        self::$client->request('GET', '/'); // чтобы открыть сессию
        self::$client->enableProfiler();

        $this->sendRequest($user, 'PATCH', $url, $data);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        /** @var User $reloadedUser */
        $reloadedUser = $this->getEntityManager()->find('VifeedUserBundle:User', $user->getId());
        $this->assertFalse($reloadedUser->isEmailConfirmed());
        $this->assertNotNull($reloadedUser->getConfirmationToken());
        $this->assertEquals('testadvertiser100@vifeed.ru', $reloadedUser->getEmail());

        // письмо отправлено
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = self::$client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();

        /** @var \Swift_Message $message */
        $message = $collectedMessages[0];
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertContains('confirm', $message->getBody());
        $this->assertEquals('testadvertiser100@vifeed.ru', array_keys($message->getTo())[0]);

        preg_match('@\/confirm\/([^"]+)"@', $message->getBody(), $matches);
        $token = $matches[1];
        $this->assertEquals($reloadedUser->getConfirmationToken(), $token);
    }

    /**
     * изменение пароля пользователя
     *
     * @dataProvider patchUserPasswordErrorsProvider
     */
    public function testPatchUserPasswordErrors($data, $errors)
    {
        $user = self::$parameters['fixtures']['users'][1];

        $url = self::$router->generate('api_patch_user');

        $this->sendRequest($user, 'PATCH', $url, $data);

        $this->assertEquals(400, self::$client->getResponse()->getStatusCode());

        $content = self::$client->getResponse()->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->validateErrors($content, $errors);
    }

    /**
     * изменение пароля пользователя
     */
    public function testPatchUserPasswordOk()
    {
        $user = self::$parameters['fixtures']['users'][0];

        $url = self::$router->generate('api_patch_user');

        $data = ['change_password' => [
              'currentPassword' => '12345',
              'plainPassword'   => [
                    'first'  => 'aaabbb',
                    'second' => 'aaabbb'
              ]

        ]];

        $this->sendRequest($user, 'PATCH', $url, $data);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        return $user;
    }

    /**
     * @depends testPatchUserPasswordOk
     */
    public function testPasswordChanged($user)
    {
        $url = self::$router->generate('api_delete_users_token');
        $this->sendRequest($user, 'DELETE', $url);

        $url = self::$router->generate('sign_in');
        $data = ['_username' => 'testadvertiser1@vifeed.ru',
                 '_password' => 'aaabbb'];
        self::$client->request('POST', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(self::$client->getContainer()->get('security.context')->isGranted('ROLE_USER'));
    }

    /**
     * запрос информации о компании, когда её нет
     */
    public function testGetUserCompanyEmpty()
    {
        $user = self::$parameters['fixtures']['users'][0];

        $url = self::$router->generate('api_get_company');
        $this->sendRequest($user, 'GET', $url);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertEquals('', $content);
    }

    /**
     * запрос информации о компании
     */
    public function testGetUserCompanyOK()
    {
        $user = self::$parameters['fixtures']['users'][1];

        $url = self::$router->generate('api_get_company');
        $this->sendRequest($user, 'GET', $url);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $content);
        $keys = ['system', 'name', 'contact_name', 'position', 'address', 'phone', 'inn', 'kpp', 'bic',
                 'bank_account', 'correspondent_account', 'is_approved'];
        $this->assertArrayHasOnlyKeys($keys, $content);

        $this->assertEquals('УСН', $content['system']);
        $this->assertEquals('ООО компания', $content['name']);
    }

    /**
     * ошибки формы
     */
    public function testPutCompanyOk()
    {
        $user = self::$parameters['fixtures']['users'][0];

        $url = self::$router->generate('api_put_company');

        $data = [
              'company' => [
                    'system'               => 'ОСН',
                    'name'                 => 'компания',
                    'contactName'          => 'Иван',
                    'position'             => 'бухгалтер',
                    'address'              => 'адрес',
                    'phone'                => '111',
                    'inn'                  => '123',
                    'kpp'                  => '123',
                    'bic'                  => '123',
                    'bankAccount'          => '123',
                    'correspondentAccount' => '123',
              ]
        ];

        $this->sendRequest($user, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);

        $this->assertEquals('ОСН', $content['system']);
        $this->assertEquals('компания', $content['name']);
    }

    /**
     *
     */
    public function testPutCompanyErrors()
    {
        $user = self::$parameters['fixtures']['users'][3];

        $url = self::$router->generate('api_put_company');

        $data = [
              'company' => [
                    'system' => 'FFF',
              ]
        ];

        $this->sendRequest($user, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->validateErrors($content, [
              'name'        => 'Значение не должно быть пустым.',
              'contactName' => 'Значение не должно быть пустым.',
              'address'     => 'Значение не должно быть пустым.',
              'phone'       => 'Значение не должно быть пустым.',
              'system'      => 'Значение недопустимо.',
        ]);
    }

    /**
     * редактирование компании. После изменения контактных данных флаг approved не сбрасывается
     */
    public function testEditCompany1()
    {
        /** @var User $user */
        $user = self::$parameters['fixtures']['users'][1];

        $this->assertEquals(true, $user->getCompany()->isApproved());

        $url = self::$router->generate('api_put_company');

        $data = [
              'company' => [
                    'contactName' => 'Вася',
                    'position'    => 'директор',
                    'phone'       => '54321'
              ]
        ];

        $this->sendRequest($user, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);

        $this->assertEquals('ООО компания', $content['name']);
        $this->assertEquals('директор', $content['position']);
        $this->assertEquals(true, $content['is_approved']);
    }

    /**
     * редактирование компании. После изменения основных данных флаг approved сбрасывается
     */
    public function testEditCompany2()
    {
        /** @var User $user */
        $user = self::$parameters['fixtures']['users'][1];
        $this->assertEquals(true, $user->getCompany()->isApproved());

        $url = self::$router->generate('api_put_company');

        $data = [
              'company' => [
                    'system'   => 'ОСН',
                    'name' => 'blabla'
              ]
        ];

        $this->sendRequest($user, 'PUT', $url, $data);

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $content = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $content);

        $this->assertEquals('ОСН', $content['system']);
        $this->assertEquals('blabla', $content['name']);
        $this->assertEquals(false, $content['is_approved']);

    }

    /**
     * дата-провайдер для testPatchUserProfileErrors
     *
     * @return array
     */
    public function patchUserErrorsProvider()
    {
        return [
              [
                    ['profile' => [
                          'password' => 'vvv'
                    ]],
                    ['Эта форма не должна содержать дополнительных полей.']
              ],
              [
                    ['profile' => [
                          'email' => ''
                    ]],
                    ['email' => 'Пожалуйста, укажите Ваш email']
              ],
              [
                    ['profile' => [
                          'email' => 'vvv'
                    ]],
                    ['email' => 'Email в неправильном формате']
              ],
              [
                    ['profile' => [
                          'email' => 'testadvertiser2@vifeed.ru'
                    ]],
                    ['email' => 'Email уже используется']
              ],
        ];
    }

    /**
     * data-provider для testUserRegisterErrors
     *
     * @return array
     */
    public function patchUserPasswordErrorsProvider()
    {
        return [
              [
                    ['change_password' => [
                          'currentPassword' => '12345',
                    ]],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Пожалуйста, укажите пароль'
                          ]
                    ]]
              ],
              [
                    ['change_password' => [
                    ]],
                    ['currentPassword' => 'Значение должно быть текущим паролем пользователя.']
              ],
              [
                    ['change_password' => [
                          'currentPassword' => '111',
                    ]],
                    ['currentPassword' => 'Значение должно быть текущим паролем пользователя.']
              ],
              [
                    ['change_password' => [
                          'currentPassword' => '12345',
                          'plainPassword'   => [
                                'first' => 'aaa'
                          ]
                    ]],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Введенные пароли не совпадают'
                          ]
                    ]]
              ],
              [
                    ['change_password' => [
                          'currentPassword' => '12345',
                          'plainPassword'   => [
                                'first'  => 'aaa',
                                'second' => 'aaa'
                          ]
                    ]],
                    ['plainPassword' => [
                          'children' => [
                                'first' => 'Пароль слишком короткий'
                          ]
                    ]]
              ],
        ];
    }

    protected static function loadTestFixtures()
    {
        $userManager = self::getContainer()->get('fos_user.user_manager');
        $tokenManager = self::getContainer()->get('vifeed.user.wsse_token_manager');
        $em = self::getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user1 */
        $user1 = $userManager->createUser();
        $user1
              ->setEmail('testadvertiser1@vifeed.ru')
              ->setUsername('testadvertiser1@vifeed.ru')
              ->setFirstName('aaa')
              ->setSurname('bbb')
              ->setPhone(123)
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($user1);
        $tokenManager->createUserToken($user1->getId());

        /** @var User $user2 */
        $user2 = $userManager->createUser();
        $user2
              ->setEmail('testadvertiser2@vifeed.ru')
              ->setUsername('testadvertiser2@vifeed.ru')
              ->setFirstName('aaa')
              ->setSurname('bbb')
              ->setPhone(123)
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_PUBLISHER)
              ->setPlainPassword('12345');

        $company = new Company();
        $company->setSystem('УСН')
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
                ->setIsApproved(true);

        $user2->setCompany($company);
        $em->persist($company);
        $userManager->updateUser($user2);
        $tokenManager->createUserToken($user2->getId());

        /** @var User $user3 */
        $user3 = $userManager->createUser();
        $user3
              ->setEmail('testadvertiser3@vifeed.ru')
              ->setUsername('testadvertiser3@vifeed.ru')
              ->setFirstName('aaa')
              ->setSurname('bbb')
              ->setPhone(123)
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setEmailConfirmed(true)
              ->setPlainPassword('12345');

        $userManager->updateUser($user3);
        $tokenManager->createUserToken($user3->getId());

        /** @var User $user4 */
        $user4 = $userManager->createUser();
        $user4
              ->setEmail('testadvertiser4@vifeed.ru')
              ->setUsername('testadvertiser4@vifeed.ru')
              ->setFirstName('aaa')
              ->setSurname('bbb')
              ->setPhone(123)
              ->setBalance(100)
              ->setEnabled(true)
              ->setType(User::TYPE_ADVERTISER)
              ->setPlainPassword('12345');

        $userManager->updateUser($user4);
        $tokenManager->createUserToken($user4->getId());


        $em->flush();

        return [
              'users' => [$user1, $user2, $user3, $user4]
        ];

    }
}
