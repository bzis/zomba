<?php

namespace Vifeed\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Vifeed\UserBundle\Entity\User;
use Vifeed\UserBundle\OAuth\OAuthProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SocialController extends Controller
{

    /**
     * @Template
     *
     * @throws \Exception
     * @internal param \Symfony\Component\HttpFoundation\Request $request
     *
     * todo: возможный вариант: ?error=invalid_request&error_description=Invalid+display+parameter
     * todo: подумать про обработку ошибок от ВК
     */
    public function linkUserAction()
    {
        $request = $this->getRequest();
        $providerName = $request->get('provider');
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');

        /** @var $OAuthFactory OAuthProviderFactory */
        $OAuthFactory = $this->container->get('vifeed.oauth.provider.factory');

        $OAuthProvider = $OAuthFactory->getProvider($providerName);
        foreach ($OAuthProvider->getRequiredParametersFromRequest() as $name) {
            if ($request->get($name) === '') {
                throw new \Exception('Не получен необходимый параметр ' . $name);
            }
            $OAuthProvider->setParameter($name, $request->get($name));
        }

        $extId = $OAuthProvider->getUserId();

        /** @var $user User */
        $user = $this->getUser();
        $userRepo = $entityManager->getRepository('VifeedUserBundle:User');
        $checkUserByExtId = $userRepo->findOneBy(array($user::getSocialIdName($providerName) => $extId));
        if ($checkUserByExtId && $checkUserByExtId !== $user) {
            throw new \Exception('Другой пользователь уже привязал этот аккаунт к своему профилю');
        }
        $user->setSocialID($providerName, $extId)
              ->setSocialDataByProvider($providerName, array('token' => $OAuthProvider->getAccessToken()));

        $entityManager->persist($user);
        $entityManager->flush();

        return array();
    }

}
