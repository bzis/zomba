<?php

namespace Vifeed\GeoBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Vifeed\GeoBundle\Entity\Country;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CountryController
 *
 * @package Vifeed\GeoBundle\Controller
 */
class CountryController extends FOSRestController
{

    /**
     * Список стран
     *
     * @ApiDoc(
     *     section="Geo API",
     *     resource=true,
     *     output={
     *          "class"="Vifeed\GeoBundle\Entity\Country",
     *          "groups"={"default"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method"
     *     }
     * )
     *
     * @return Response
     */
    public function getCountriesAction()
    {
        /** @var Country[] $data */
        $data = $this->getDoctrine()->getRepository('VifeedGeoBundle:Country')->findBy([], ['name' => 'asc']);

        $context = new SerializationContext();
        $context->setGroups(['default']);

        $view = new View($data);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

    /**
     * Информация о стране по id
     *
     * @param Country $country
     *
     * @Rest\Get("countries/{id}", requirements={"id"="\d+"})
     * @ParamConverter("country", class="VifeedGeoBundle:Country")
     * @ApiDoc(
     *     section="Campaign API",
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id страны"}
     *     },
     *     output={
     *          "class"="Vifeed\GeoBundle\Entity\Country",
     *          "groups"={"default"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when campaign not found"
     *     }
     * )
     *
     * @return Response
     */
    public function getCountryAction(Country $country)
    {
        $context = new SerializationContext();
        $context->setGroups(['default']);

        $view = new View($country);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

}
