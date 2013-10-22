<?php

namespace Vifeed\UserBundle\OAuth\Provider;

class VKOAuthProvider extends AbstractOAuthProvider
{
    protected $codeVarName = 'code';
    protected $accessTokenUrl = 'https://oauth.vk.com/access_token';

    /* что нужно получить из конфига */
    protected $requiredParametersFromConfig = array(
        'VKAppID',
        'VKAppSecret'
    );

    /* что нужно получить из реквеста */
    protected $requiredParametersFromRequest = array(
        'code'
    );

    protected $extUserId;


    /**
     * {@inheritdoc}
     */
    protected function prepareAccessTokenParams()
    {
        return array(
            'code'          => $this->parameters['code'],
            'client_id'     => $this->parameters['VKAppID'],
            'client_secret' => $this->parameters['VKAppSecret'],
            'redirect_uri'  => $this->parameters['redirectUri'],
        );
    }

    /**
     * обрабатывает полученный токен
     * должен прийти json вида {"access_token":"533bacf01e11f55b536a565b57531ac114461ae8736d6506a3", "expires_in":0, "user_id":66748}
     *
     * @return string
     *
     * todo: здесь можно получить сообщение об ошибке вида {"error":"invalid_grant","error_description":"Code is expired."}
     */
    public function getAccessToken()
    {
        if ($this->accessToken === null) {
            $response = $this->requestAccessToken($this->prepareAccessTokenParams());
            if (!isset($response['access_token'])) {
                throw new \LogicException('Ошибка получения токена');
            }
            $this->accessToken = $response['access_token'];
            $this->extUserId = $response['user_id']; // такой костыль. Пока не придумал, как сделать лучше
        }

        return $this->accessToken;
    }

    /**
     * Возвращает ID пользователя в социальной сети
     * @return int
     */
    public function getUserId()
    {
        if ($this->extUserId === null) {
            $this->getAccessToken();
        }

        return $this->extUserId;
    }

}
