<?php

namespace Vifeed\SystemBundle\DependencyInjection\Compiler;

/**
 * Class DoctrineEntityListenerPass
 * @package Vifeed\SystemBundle\DependencyInjection\Compiler
 */
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEntityListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getDefinition('doctrine.orm.default_configuration');
        $config->addMethodCall(
               'setEntityListenerResolver',
               array(new Reference('vifeed.doctrine.entity_listener_resolver'))
        );

        $definition = $container->getDefinition('vifeed.doctrine.entity_listener_resolver');
        $services = $container->findTaggedServiceIds('doctrine.entity_listener');

        foreach ($services as $service => $attributes) {
            $definition->addMethodCall(
                       'addMapping',
                       array($container->getDefinition($service)->getClass(), $service)
            );
        }
    }
}