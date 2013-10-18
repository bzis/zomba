<?php

namespace Vifeed\UserBundle\OAuth\Provider;

use Symfony\Component\HttpFoundation\Request,
      Vifeed\UserBundle\Entity\User;

class VKOAuthProvider extends AbstractOAuthProvider
{
    protected
          $codeVarName = 'code',
          $accessTokenUrl = 'https://oauth.vk.com/access_token',

          $apiUrl = 'https://api.vk.com/method/';

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
     * @return array
     *
     * Формат присылаемых данных:
     * array (
     *   'uid' => int 5312944
     *   'first_name' => string 'Борис' (length=10)
     *   'last_name' => string 'Зискин' (length=12)
     *   'nickname' => string '' (length=0)
     *   'screen_name' => string 'bziskin' (length=7)
     *   'sex' => int 2
     *   'bdate' => string '14.2.1987' (length=9)
     *   'city' => string '1' (length=1)
     *   'country' => string '1' (length=1)
     *   'timezone' => int 3
     *   'photo' => string 'http://cs10510.userapi.com/u5312944/e_e9b44ee8.jpg' (length=50)
     *   'photo_medium' => string 'http://cs10510.userapi.com/u5312944/b_33c537bf.jpg' (length=50)
     *   'photo_big' => string 'http://cs10510.userapi.com/u5312944/a_877b9fe4.jpg' (length=50)
     * )
     */
    public function getUserInfo()
    {
        $raw = $this->requestUserInfo();
        $info = array(
            'ext_id' => $this->extUserId,
            'name'   => trim($raw['first_name'] . ' ' . $raw['last_name']),
            'email'  => null, // для совместимости
            'raw'    => $raw
        );
        return $info;
    }

    protected function prepareAccessTokenParams()
    {
        $this->accessTokenParams = array(
            'code'          => $this->parameters['code'],
            'client_id'     => $this->parameters['VKAppID'],
            'client_secret' => $this->parameters['VKAppSecret'],
            'redirect_uri'  => $this->parameters['redirectUri'],
        );
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        if ($this->accessToken === null) {
            $this->prepareAccessTokenParams();
            $response = $this->requestAccessToken();
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

    /**
     * @return array
     */
    protected function requestUserInfo()
    {
        $params = array(
            'token'  => $this->getAccessToken(),
            'uids'   => $this->extUserId, // он появляется только после getAccessToken
            'fields' => 'screen_name'
//            'fields' => 'nickname, screen_name, sex, bdate, city, country, timezone, photo_50, photo_100, photo_200_orig, has_mobile, contacts, education, online, counters, relation, last_seen, status, can_write_private_message, can_see_all_posts, can_see_audio, can_post, universities, schools,verified '
        );
        $url = $this->apiUrl . 'users.get?' . http_build_query($params);

        $response = $this->httpRequest($url);
        $json = json_decode($response->getContent(), true);
        if (!isset($json['response'][0])) {
            throw new \LogicException('Ошибка получения данных');
        }
        $info = $json['response'][0];

        return $info;
    }

}
