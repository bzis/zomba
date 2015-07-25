<?php

namespace Vifeed\GeoBundle\Tests\Controller;

use Vifeed\GeoBundle\Entity\Country;
use Vifeed\SystemBundle\Tests\ApiTestCase;

class CountryControllerTest extends ApiTestCase
{

    /**
     * спиисок стран без авторизации
     */
    public function testGetCountriesUnauthorized()
    {
        $url = self::$router->generate('api_get_countries');

        self::$client->request('GET', $url);
        $this->assertEquals(403, self::$client->getResponse()->getStatusCode());
    }

    /**
     * Список стран
     */
    public function testGetCountriesOk()
    {
        $url = self::$router->generate('api_get_countries');

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

        $this->assertEquals('Белоруссия', $data[0]['name']);
        $this->assertEquals('Россия', $data[1]['name']);
    }

    public function testGetCountryOk()
    {
        /** @var Country $country */
        $country = self::$parameters['fixtures']['countries'][0];

        $url = self::$router->generate('api_get_country', ['id' => $country->getId()]);
        $this->sendRequest(self::$testAdvertiser, 'GET', $url);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, JSON_UNESCAPED_UNICODE);
        $this->assertCount(2, $data);
        $keys = array_keys($data);
        $this->assertEquals(['id', 'name'], $keys);

        $this->assertEquals('Россия', $data['name']);
    }

    protected static function loadTestFixtures()
    {
        $country1 = new Country();
        $country1->setName('Россия');

        self::$em->persist($country1);

        $country2 = new Country();
        $country2->setName('Белоруссия');

        self::$em->persist($country2);

        self::$em->flush();

        return ['countries' => [$country1, $country2]];
    }

}
