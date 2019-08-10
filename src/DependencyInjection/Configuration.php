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
                ->scalarNode('default_connection')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultValue('default')
                ->end()
                ->arrayNode('connections')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('database')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('user')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('password')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('orm')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                        ->end()
                        ->arrayNode('types')
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->scalarPrototype()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !class_exists($v);
                                    })
                                    ->thenInvalid('The Odoo ORM type class %s does not exist')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('managers')
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('connection')
                                        ->defaultNull()
                                    ->end()
                                    ->arrayNode('mapping')
                                        ->scalarPrototype()
                                            ->validate()
                                                ->ifTrue(function ($v) {
                                                    return !is_dir($v);
                                                })
                                                ->thenInvalid('The Odoo model directory %s was not found')
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->booleanNode('load_defaults')
                                        ->defaultTrue()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
