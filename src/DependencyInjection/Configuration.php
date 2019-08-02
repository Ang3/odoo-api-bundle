<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use InvalidArgumentException;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
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
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('database')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('user')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->scalarNode('password')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->defaultNull()
                ->end()
                ->arrayNode('models')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(function ($value) {
                                return false === class_exists($value);
                            })
                            ->then(function ($value) {
                                throw new InvalidArgumentException(sprintf('The Odoo model class "%s" was not found', $value));
                            })
                        ->end()
                        ->validate()
                            ->ifTrue(function ($value) {
                                return false === in_array(RecordInterface::class, class_implements($value));
                            })
                            ->then(function ($value) {
                                throw new InvalidArgumentException(sprintf('The Odoo model class "%s" must implement interface "%s"', $value, RecordInterface::class));
                            })
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
