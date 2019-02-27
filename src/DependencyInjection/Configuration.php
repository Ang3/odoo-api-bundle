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
                ->scalarNode('url')
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('database')
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('user')
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('password')
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
