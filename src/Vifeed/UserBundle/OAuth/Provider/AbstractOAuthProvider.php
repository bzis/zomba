<?php

namespace Vifeed\UserBundle\OAuth\Provider;

use Buzz\Client\AbstractClient;
use Buzz\Client\Curl;
use Buzz\Message\Request;
use Buzz\Message\Response;

abstract class AbstractOAuthProvider
{
    /** @var  AbstractClient */
    protected $httpClient;
    protected $accessToken;

    protected $codeVarName;
    protected $requiredParametersFromConfig = array();
    protected $requiredParametersFromRequest = array();
    protected $parameters = array();

    protected $accessTokenUrl;
    protected $accessTokenParams = array();

    protected $apiUrl;

    public function __construct()
    {
        $this->setHttpClient(new Curl());
    }

    /**
     * Используется в тестах
     * @param $httpClient
     * @return void
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCodeVarName()
    {
        return $this->codeVarName;
    }

    /**
     * Запрашивает токен и возвращает массив из полученных данных
     * @return array
     */
    protected function requestAccessToken()
    {
        $url = $this->accessTokenUrl . '?' . http_build_query($this->accessTokenParams);
        $response = $this->httpRequest($url);

        if (false !== strpos($response->getHeader('Content-Type'), 'application/json')) {
            $responseParams = json_decode($response->getContent(), true);
        } else {
            parse_str($response->getContent(), $responseParams);
        }

        return $responseParams;
    }

    /**
     * Отправляет HTTP request
     *
     * @param string $url     The url to fetch
     * @param string $content The content of the request
     * @param string $method  The HTTP method to use
     *
     * @return Response
     */
    protected function httpRequest($url, $content = null, $method = null)
    {
        if (null === $method) {
            $method = (null === $content) ? Request::METHOD_GET : Request::METHOD_POST;
        }
        $request = new Request($method, $url);
        $response = new Response();
        $request->setContent($content);
        $this->httpClient->send($request, $response);
        return $response;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * список необходимых параметров из конфига
     *
     * @return array
     */
    public function getRequiredParametersFromConfig()
    {
        return $this->requiredParametersFromConfig;
    }

    /**
     * список необходимых параметров из запроса
     *
     * @return array
     */
    public function getRequiredParametersFromRequest()
    {
        return $this->requiredParametersFromRequest;
    }

    /**
     * @abstract
     * @return array
     *
     * формат возвращаемых данных:
     * array(
     *  'ext_id' => ID в социальной сети
     *  'name'   => имя в формате "имя фамилия"
     *  'gender' => пол. константа из класса User или null
     *  'email'  => email
     *  'raw'    => массив данных в таком виде, как его отдаёт социальная сеть
     * )
     */
    abstract public function getUserInfo();

    /**
     * Возвращает ID пользователя в социальной сети
     * @abstract
     * @return int
     */
    abstract public function getUserId();

    /**
     * @abstract
     * @return string
     */
    abstract public function getAccessToken();

    /**
     * @abstract
     * @return void
     */
    abstract protected function prepareAccessTokenParams();

    /**
     * @abstract
     * @return array
     */
    abstract protected function requestUserInfo();

}
