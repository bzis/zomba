<?php

namespace Vifeed\UserBundle\Manager;

class VkManager extends AbstractSocialManager
{

    protected $apiUrl = 'https://api.vk.com/method/';

    /**
     * @param array $ids
     *
     * @throws \LogicException
     * @return array
     *
     * Формат присылаемых данных:
     * array (
     *   'uid' => 5312944
     *   'first_name' => 'Борис'
     *   'last_name' => 'Зискин'
     *   'nickname' => ''
     *   'screen_name' => 'bziskin'
     *   'sex' => 2
     *   'bdate' => '14.2.1987'
     *   'city' => '1'
     *   'country' => '1'
     *   'timezone' => 3
     *   'photo' => 'http://cs10510.userapi.com/u5312944/e_e9b44ee8.jpg'
     *   'photo_medium' => 'http://cs10510.userapi.com/u5312944/b_33c537bf.jpg'
     *   'photo_big' => 'http://cs10510.userapi.com/u5312944/a_877b9fe4.jpg'
     * )
     */
    public function getUserInfo(array $ids)
    {
        $params = array(
            'token'  => $this->token,
            'uids'   => $ids,
            // он появляется только после getAccessToken
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

    /**
     * {@inheritdoc}
     */
    public function addVideo($video)
    {
        // TODO: Implement addVideo() method.
    }
}
 