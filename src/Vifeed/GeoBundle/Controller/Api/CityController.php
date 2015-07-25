<?php

namespace Vifeed\GeoBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vifeed\GeoBundle\Entity\City;
use Vifeed\GeoBundle\Entity\Country;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class CityController
 *
 * @package Vifeed\GeoBundle\Controller
 */
class CityController extends FOSRestController
{

    /**
     * Список городов по стране
     *
     * @param Country $country
     *
     * @Rest\Get("/countries/{id}/cities", requirements={"id"="\d+"})
     * @ParamConverter("country", class="VifeedGeoBundle:Country")
     * @ApiDoc(
     *     section="Geo API",
     *     resource=true,
     *     requirements={
     *       {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="id страны"}
     *     },
     *     output={
     *          "class"="Vifeed\GeoBundle\Entity\City",
     *          "groups"={"default"}
     *     },
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to use this method",
     *         404="Returned when country not found"
     *     }
     * )
     *
     * @return Response
     */
    public function getCitiesByCountryAction(Country $country)
    {
        /** @var City[] $data */
        $data = $this->getDoctrine()->getRepository('VifeedGeoBundle:City')->findBy(['country' => $country]);

        $context = new SerializationContext();
        $context->setGroups(['default']);

        $view = new View($data);
        $view->setSerializationContext($context);

        return $this->handleView($view);
    }

}
