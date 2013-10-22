<?php

namespace Vifeed\UserBundle\OAuth;

use Symfony\Component\Routing\Router;

class OAuthProviderFactory
{
    private $socialData;

    /**
     * @param array                             $socialData идентификационные данные социальных сетей
     * @param \Symfony\Component\Routing\Router $router
     */
    public function __construct($socialData, Router $router)
    {
        $this->socialData = $socialData;
        $this->router = $router;
    }

    /**
     * @param $name
     *
     * @return \Vifeed\UserBundle\OAuth\Provider\AbstractOAuthProvider
     */
    public function getProvider($name)
    {
        $providerName = __NAMESPACE__ . '\Provider\\' . $name . 'OAuthProvider';
        if (!class_exists($providerName)) {
            throw new \Exception('Неправильный провайдер социальной сети');
        }

        /** @var $provider \Vifeed\UserBundle\OAuth\Provider\AbstractOAuthProvider */
        $provider = new $providerName();

        foreach ($provider->getRequiredParametersFromConfig() as $name) {
            if (!isset($this->socialData[$name])) {
                throw new \Exception('Не определён необходимый параметр ' . $name);
            }
            $provider->setParameter($name, $this->socialData[$name]);
        }

        $redirectUri = $this->router->generate('user_social_link', array('provider' => 'VK'), true);
        $provider->setParameter('redirectUri', $redirectUri);

        return $provider;
    }
}
