<?php

namespace Vifeed\UserBundle\DependencyInjection\Security;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class WsseFactory implements SecurityFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param                  $id
     * @param                  $config
     * @param                  $userProvider
     * @param                  $defaultEntryPoint
     *
     * @return array
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.wsse.' . $id;
        $container
              ->setDefinition($providerId, new DefinitionDecorator('wsse.security.authentication.provider'))
              ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.wsse.' . $id;
        $listener = $container->setDefinition(
            $listenerId,
            new DefinitionDecorator('wsse.security.authentication.listener')
        );

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'wsse';
    }

    /**
     * @param NodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }

}
