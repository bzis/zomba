<?php

namespace Vifeed\SystemBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Vifeed\SystemBundle\DependencyInjection\Compiler\DoctrineEntityListenerPass;

class VifeedSystemBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineEntityListenerPass());
    }
}
