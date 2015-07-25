<?php

namespace Vifeed\PaymentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vifeed_payment');
        $rootNode
              ->children()
                ->arrayNode('ip_filter')
                    ->children()
                        ->integerNode('short_skip_time')->defaultValue(600)->end()
                        ->integerNode('long_skip_time')->defaultValue(86400)->end()
                        ->integerNode('long_skip_views')->defaultValue(5)->end()
                        ->integerNode('timeout_per_campaign')->defaultValue(0)->end()
                    ->end()
                ->end()
                ->integerNode('max_workers')->defaultValue(2)->end()
              ->end();
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
