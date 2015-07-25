<?php

namespace Vifeed\GeoBundle\Provider;

interface GeoProviderInterface
{
    /**
     * @param int $ip IP
     *
     * @return int|null
     */
    public function getCityByIp($ip);

    /**
     * @param int $city_id city_id
     *
     * @return int|null
     */
    public function getCountryByCity($city_id);

    /**
     * @param int $ip IP
     *
     * @return int|null
     */
    public function getCountryByIp($ip);
} 