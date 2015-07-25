<?php

namespace Vifeed\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class OverrideServiceCompilerPass
 *
 * @package Vifeed\UserBundle\DependencyInjection\Compiler
 */
class OverrideServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        try {
            $definition = $container->getDefinition('fos_user.listener.email_confirmation');
            $definition->setClass('Vifeed\UserBundle\EventListener\EmailConfirmationListener');
        } catch (InvalidArgumentException $e) {

        }
    }
}
 