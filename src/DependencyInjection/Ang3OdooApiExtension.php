<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Bundle\OdooApiBundle\ClientRegistry;
use Ang3\Component\Odoo\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Joanis ROUANET
 */
class Ang3OdooApiExtension extends Extension
{
    private const MONOLOG_DEFINITION = 'monolog.logger.odoo';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('ang3_odoo_api.parameters', $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->loadClientRegistry($container, $config['connections'], $config['default_connection']);
    }

    /**
     * Load clients instances from connections params.
     */
    public function loadClientRegistry(ContainerBuilder $container, array $connections, string $defaultConnection): void
    {
        if (!array_key_exists($defaultConnection, $connections)) {
            throw new InvalidArgumentException(sprintf('The default Odoo connection "%s" is not configured', $defaultConnection));
        }

        $registry = $container->getDefinition(ClientRegistry::class);
        $odooLogger = $container->hasDefinition(self::MONOLOG_DEFINITION) ? new Reference(self::MONOLOG_DEFINITION) : null;

        foreach ($connections as $name => $params) {
            $client = new Definition(Client::class, [[
                $params['url'],
                $params['database'],
                $params['user'],
                $params['password'],
            ], $odooLogger]);

            $clientName = sprintf('ang3_odoo_api.client.%s', $name);
            $container->setDefinition($clientName, $client);
            $container->registerAliasForArgument($clientName, Client::class, "$name.client");

            if ($name === $defaultConnection) {
                $container->setDefinition(Client::class, $client);
                $container->setAlias('ang3_odoo_api.client', $clientName);
                $container->registerAliasForArgument($clientName, Client::class, 'client');
            }

            $clientReference = new Reference($clientName);
            $registry->addMethodCall('set', [$name, $clientReference]);
        }
    }
}
