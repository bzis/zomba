<?php

namespace Vifeed\UserBundle\Manager;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\Request;
use Buzz\Message\Response;

abstract class AbstractSocialManager
{
    protected $token;
    protected $apiUrl;

    /**
     * Отправляет HTTP request
     *
     * @param string $url     The url to fetch
     * @param string $content The content of the request
     * @param string $method  The HTTP method to use
     *
     * @return Response
     */
    protected function httpRequest($url, $content = null, $method = Request::METHOD_GET)
    {
        $browser = new Browser(new Curl);
        $response = $browser->call($url, $method, array(), $content);

        return $response;
    }


    /**
     * получить информацию о пользователе
     *
     * @param array $ids
     *
     * @return array
     */
    abstract public function getUserInfo(array $ids);

    /**
     * добавить видео
     *
     * @param $video
     *
     * @return mixed
     */
    abstract public function addVideo($video);


}
 