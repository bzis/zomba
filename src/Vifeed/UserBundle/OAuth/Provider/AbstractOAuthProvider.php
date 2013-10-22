<?php

namespace Vifeed\UserBundle\OAuth\Provider;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\Request;
use Buzz\Message\Response;

abstract class AbstractOAuthProvider
{
    protected $accessToken;

    protected $codeVarName;
    protected $requiredParametersFromConfig = array();
    protected $requiredParametersFromRequest = array();
    protected $parameters = array();

    protected $accessTokenUrl;

    public function getCodeVarName()
    {
        return $this->codeVarName;
    }

    /**
     * Запрашивает токен и возвращает массив из полученных данных
     *
     * @param array $params переменные запроса
     *
     * @return array
     */
    protected function requestAccessToken($params)
    {
        $url = $this->accessTokenUrl . '?' . http_build_query($params);
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
    protected function httpRequest($url, $content = null, $method = Request::METHOD_GET)
    {
        $browser = new Browser(new Curl);
        $response = $browser->call($url, $method, array(), $content);

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



}
