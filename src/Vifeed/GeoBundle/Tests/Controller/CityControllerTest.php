<?php

namespace Vifeed\GeoBundle\Tests\Controller;

use Vifeed\GeoBundle\Entity\City;
use Vifeed\GeoBundle\Entity\Country;
use Vifeed\SystemBundle\Tests\ApiTestCase;

class CityControllerTest extends ApiTestCase
{

    /**
     * список городов без авторизации
     */
    public function testGetCitiesUnauthorized()
    {
        $country = self::$parameters['fixtures']['countries'][1];

        $url = self::$router->generate('api_get_cities_by_country', ['id' => $country->getId()]);

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * список городов с несуществующим id страны
     */
    public function testGetCitiesWrongCountry()
    {
        $url = self::$router->generate('api_get_cities_by_country', ['id' => -1]);

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * список городов для страны без городов
     */
    public function testGetCitiesEmpty()
    {
        $country = self::$parameters['fixtures']['countries'][1];

        $url = self::$router->generate('api_get_cities_by_country', ['id' => $country->getId()]);

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);
        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertInternalType('array', $data);
        $this->assertEmpty($data);
    }

    /**
     * Список городов
     */
    public function testGetCitiesOk()
    {
        $country = self::$parameters['fixtures']['countries'][0];

        $url = self::$router->generate('api_get_cities_by_country', ['id' => $country->getId()]);

        $this->sendRequest(self::$testAdvertiser, 'GET', $url);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);

        $this->assertInternalType('array', $data);
        $this->assertCount(2, $data);

        $keys = array_keys($data[0]);
        $this->assertEquals(['id', 'name'], $keys);

        $this->assertEquals('Москва', $data[0]['name']);
        $this->assertEquals('Санкт-Петербург', $data[1]['name']);
    }

    protected static function loadTestFixtures()
    {
        $country1 = new Country();
        $country1->setName('Россия');
        self::$em->persist($country1);

        $country2 = new Country();
        $country2->setName('Белоруссия');
        self::$em->persist($country2);

        $city1 = new City();
        $city1->setCountry($country1)
              ->setName('Москва');
        self::$em->persist($city1);

        $city2 = new City();
        $city2->setCountry($country1)
              ->setName('Санкт-Петербург');
        self::$em->persist($city2);

        self::$em->flush();

        return ['countries' => [$country1, $country2]];
    }

}
