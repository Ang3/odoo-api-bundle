<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Bundle\OdooApiBundle\ClientRegistry;
use Ang3\Component\Odoo\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
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

        // Bundle parameters
        $container->setParameter('ang3_odoo_api.parameters', $config);

        // Chargement des connections
        $this->loadClientRegistry($container, $config['connections'], $config['default_connection']);

        // Définition d'un chargeur de fichier YAML
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Chargement des services
        $loader->load('services.yaml');
    }

    /**
     * Load clients instances from connections params.
     */
    public function loadClientRegistry(ContainerBuilder $container, array $connections, string $defaultConnection): void
    {
        // Si la connexion par défat n'existe pas
        if (!array_key_exists($defaultConnection, $connections)) {
            throw new InvalidArgumentException(sprintf('The default Odoo connection "%s" is not configured', $defaultConnection));
        }

        // Création de la définition du registre
        $registry = new Definition(ClientRegistry::class);
        $odooLogger = $container->hasDefinition(self::MONOLOG_DEFINITION) ? new Reference(self::MONOLOG_DEFINITION) : null;

        // Pour chaque conenctions
        foreach ($connections as $name => $params) {
            // Création de la définition
            $client = new Definition(Client::class, [[
                $params['url'],
                $params['database'],
                $params['user'],
                $params['password'],
            ], $odooLogger]);

            // Définition du nom du client
            $clientName = sprintf('ang3_odoo_api.client.%s', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($clientName, $client);
            $container->registerAliasForArgument($clientName, Client::class, "$name.client");

            // S'il s'agit du client par défaut
            if ($name === $defaultConnection) {
                // Enregistrement du client par défaut
                $container->setDefinition(Client::class, $client);

                // Enregistrement du client par défaut
                $container->setAlias('ang3_odoo_api.client', $clientName);
                $container->registerAliasForArgument($clientName, Client::class, 'client');
            }

            // Retour de la référence du service client
            $clientReference = new Reference($clientName);

            // Enregistrement de la connection dans le registre
            $registry->addMethodCall('set', [$name, $clientReference]);
        }

        // Enregistrement du registre dans le container
        $container->setDefinition(ClientRegistry::class, $registry);

        // Enregistrement de l'alias
        $container->setAlias('ang3_odoo_api.client_registry', new Alias(ClientRegistry::class, true));
    }
}
