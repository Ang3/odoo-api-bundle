<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Bundle\OdooApiBundle\ClientRegistry;
use Ang3\Component\Odoo\Client;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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
     *
     * @throws Exception on services file loading failure
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('ang3_odoo_api.parameters', $config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $this->loadClientRegistry($container, $config);
    }

    /**
     * Load clients instances from connections params.
     */
    public function loadClientRegistry(ContainerBuilder $container, array $config): void
    {
        if (!array_key_exists($config['default_connection'], $config['connections'])) {
            throw new InvalidArgumentException(sprintf('The default Odoo connection "%s" is not configured', $config['default_connection']));
        }

        $registry = $container->getDefinition(ClientRegistry::class);

        foreach ($config['connections'] as $name => $params) {
            $loggerServiceName = $params['logger'] ?: $config['default_logger'];
            $logger = $loggerServiceName ? new Reference($loggerServiceName) : null;

            $client = new Definition(Client::class, [
                [
                    $params['url'],
                    $params['database'],
                    $params['user'],
                    $params['password'],
                ],
                $logger,
            ]);

            $clientName = sprintf('ang3_odoo_api.client.%s', $name);
            $container->setDefinition($clientName, $client);
            $container->registerAliasForArgument($clientName, Client::class, "$name.client");

            if ($name === $config['default_connection']) {
                $container->setDefinition(Client::class, $client);
                $container->setAlias('ang3_odoo_api.client', $clientName);
                $container->registerAliasForArgument($clientName, Client::class, 'client');
            }

            $clientReference = new Reference($clientName);
            $registry->addMethodCall('set', [$name, $clientReference]);
        }
    }
}
