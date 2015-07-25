<?php

namespace Vifeed\TagBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Vifeed\TagBundle\Entity\Tag;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class TagController
 *
 * @package Vifeed\TagBundle\Controller
 */
class TagController extends FOSRestController
{

    /**
     * Список тегов по началу названия
     *
     * @param string $word
     *
     * @ApiDoc(
     *     section="Frontend API",
     *     resource=true
     * )
     *
     * @return Response
     */
    public function getTagsAction($word)
    {
        $word = (string) $word;
        if (mb_strlen($word) == 0) {
            throw new BadRequestHttpException('Can not be empty');
        }

        /** @var Tag[] $data */
        $tags = $this->getDoctrine()->getRepository('VifeedTagBundle:Tag')->findByWord($word);

        $tagsArr = [];

        foreach ($tags as $tag) {
            $tagsArr[] = $tag->getName();
        }

        $view = new View($tagsArr);

        return $this->handleView($view);
    }

}
