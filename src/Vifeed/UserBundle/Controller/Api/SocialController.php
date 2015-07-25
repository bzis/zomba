<?php

namespace Vifeed\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use Vifeed\UserBundle\Entity\User;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class SocialController extends FOSRestController
{

    /**
     * Отвязать аккаунт соц. сети
     *
     * @ApiDoc(
     *     section="User API"
     * )
     */
    public function unlinkUserSocialAction()
    {
        $provider = $this->getRequest()->get('provider');

        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        /** @var $user User */
        $user = $this->getUser();

        $user->setSocialID($provider, null);
        $user->removeSocialDataByProvider($provider);

        $entityManager->persist($user);
        $entityManager->flush();

        $view = new View('', 204);

        return $this->handleView($view);
    }
}
