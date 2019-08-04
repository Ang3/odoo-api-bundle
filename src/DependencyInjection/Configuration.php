<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration du bundle.
 *
 * @author Joanis ROUANET
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}.
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('ang3_odoo_api');

        $rootNode
            ->children()
                ->scalarNode('default_client')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('default')
                ->end()
                ->arrayNode('mapping')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('clients')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->defaultNull()
                            ->end()
                            ->scalarNode('database')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->defaultNull()
                            ->end()
                            ->scalarNode('username')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->defaultNull()
                            ->end()
                            ->scalarNode('password')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->defaultNull()
                            ->end()
                            ->arrayNode('mapping')
                                ->useAttributeAsKey('name')
                                ->scalarPrototype()->end()
                            ->end()
                            ->booleanNode('defaults')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
