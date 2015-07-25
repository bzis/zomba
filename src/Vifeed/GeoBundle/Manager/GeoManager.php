<?php

namespace Vifeed\GeoBundle\Manager;

use Vifeed\GeoBundle\Provider\GeoProviderInterface;

class GeoManager
{
    private $geoProvider;

    public function __construct(GeoProviderInterface $geoProvider)
    {
        $this->geoProvider = $geoProvider;
    }

    /**
     * @param int $ip IP
     *
     * @return array
     */
    public function getGeoByIp($ip)
    {
        $cityId = $this->geoProvider->getCityByIp($ip);
        if ($cityId !== null) {
            $countryId = $this->geoProvider->getCountryByCity($cityId);
        } else {
            $countryId = $this->geoProvider->getCountryByIp($ip);
        }

        return array(
              'city_id'    => $cityId,
              'country_id' => $countryId
        );
    }
}
 