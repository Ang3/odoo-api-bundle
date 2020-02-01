<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Bundle\OdooApiBundle\ClientRegistry;
use Ang3\Component\Odoo\ExternalApiClient;
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
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Bundle parameters
        $container->setParameter('ang3_odoo_api.parameters', $config);

        // Définition d'un chargeur de fichier YAML
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Chargement des services
        $loader->load('services.yml');

        // Chargement des connections
        $connections = $this->loadClientRegistry($container, $config['connections'], $config['default_connection']);
    }

    /**
     * Load clients instances from connections params.
     */
    public function loadClientRegistry(ContainerBuilder $container, array $connections, string $defaultConnection): array
    {
        // Si la connexion par défat n'existe pas
        if (!array_key_exists($defaultConnection, $connections)) {
            throw new InvalidArgumentException(sprintf('The default Odoo connection "%s" is not configured', $defaultConnection));
        }

        // Création de la définition du registre
        $registry = new Definition(ClientRegistry::class);

        // Pour chaque conenctions
        foreach ($connections as $name => &$connection) {
            // Mise-à-jour de la connexion par la référence du client associé
            $connection = $this->createClient($container, (string) $name, $connection, $name === $defaultConnection);

            // Enregistrement de la connection dans le registre
            $registry->addMethodCall('set', [$name, $connection]);
        }

        // Enregistrement du client dans le container
        $container->setDefinition(ClientRegistry::class, $registry);

        // Enregistrement de l'alias
        $container->setAlias('ang3_odoo_api.client_registry', new Alias(ClientRegistry::class, true));

        // Retour des conenctions
        return $connections;
    }

    /**
     * Create client and returns its reference.
     */
    public function createClient(ContainerBuilder $container, string $name, array $params, bool $isDefaultClient): Reference
    {
        // Création de la définition
        $definition = new Definition(ExternalApiClient::class);

        // Enregistrement des arguments de la définition
        $definition
            ->setFactory([new Reference(ExternalApiClient::class), 'createFromArray'])
            ->setArguments([$params])
        ;

        // Définition du nom du client
        $clientName = sprintf('ang3_odoo_api.client.%s', $name);

        // Enregistrement du client dans le container
        $container->setDefinition($clientName, $definition);

        // S'il s'agit du client par défaut
        if (true === $isDefaultClient) {
            // Enregistrement du client par défaut
            $container->setAlias('ang3_odoo_api.client', new Alias($clientName, true));

            // Enregistrement du client par défaut
            $container->setAlias(ExternalApiClient::class, new Alias($clientName, false));
        }

        // Retour de la référence du service client
        return new Reference($clientName);
    }
}
