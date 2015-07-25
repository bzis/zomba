<?php

namespace Vifeed\GeoBundle\Provider;

use \Doctrine\DBAL\Connection;

class NetloadGeoProvider implements GeoProviderInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Найти ID города по IP
     *
     * @param int $ip IP
     *
     * @return int|null
     */
    public function getCityByIp($ip)
    {
        $city_id = $this->getRussianCityByIp($ip);

        if ($city_id === null) {
            $city_id = $this->getWorldCityByIp($ip);
        }

        return $city_id ? : null;
    }

    /**
     * Найти ID страны по ID города
     *
     * @param int $city_id city_id
     *
     * @return int|null
     */
    public function getCountryByCity($city_id)
    {
        $sql = "SELECT country_id FROM net_city WHERE id = ?";
        $country_id = $this->connection->fetchColumn($sql, array($city_id));

        return $country_id ? : null;
    }

    /**
     * Найти ID страны по ID города
     *
     * @param int $ip IP
     *
     * @return int|null
     */
    public function getCountryByIp($ip)
    {
        $country_id = $this->getEuropeanCountryByIp($ip);

        if ($country_id === null) {
            $country_id = $this->getWorldCountryByIp($ip);
        }

        return $country_id ? : null;
    }


    /**
     * Найти ID города в России по IP
     *
     * @param int $ip IP
     *
     * @return int|null
     */
    private function getRussianCityByIp($ip)
    {
        $sql = "SELECT * FROM
                  (SELECT * FROM net_ru WHERE begin_ip <= ? ORDER BY begin_ip DESC LIMIT 1) AS t
                WHERE end_ip >= ?";
        $city_id = (int) $this->connection->fetchColumn($sql, array($ip, $ip));

        return $city_id ? : null;
    }

    /**
     * Найти ID города в мире по IP
     *
     * @param int $ip IP
     *
     * @return int|null
     */
    private function getWorldCityByIp($ip)
    {
        $sql = "SELECT * FROM
                  (SELECT * FROM net_city_ip WHERE begin_ip <= ? ORDER BY begin_ip DESC LIMIT 1) AS t
                WHERE end_ip >= ?";
        $city_id = (int) $this->connection->fetchColumn($sql, array($ip, $ip));

        return $city_id ? : null;
    }

    /**
     * Найти ID европейской страны по IP
     *
     * @param int $ip IP
     *
     * @return int|null
     */
    private function getEuropeanCountryByIp($ip)
    {
        $sql = "SELECT * FROM
                  (SELECT * FROM net_euro WHERE begin_ip <= ? ORDER BY begin_ip DESC LIMIT 1) AS t
                WHERE end_ip >= ?";
        $country_id = (int) $this->connection->fetchColumn($sql, array($ip, $ip));

        return $country_id ? : null;
    }

    /**
     * Найти ID страны в мире по IP
     *
     * @param int $ip IP
     *
     * @return int|null
     */
    private function getWorldCountryByIp($ip)
    {
        $sql = "SELECT * FROM
                  (SELECT * FROM net_country_ip WHERE begin_ip <= ? ORDER BY begin_ip DESC LIMIT 1) AS t
                WHERE end_ip >= ?";
        $country_id = (int) $this->connection->fetchColumn($sql, array($ip, $ip));

        return $country_id ? : null;
    }

}
 