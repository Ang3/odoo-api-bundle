<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Component\OdooApiClient\Factory\ApiClientFactory;
use Ang3\Bundle\OdooApiBundle\Doctrine\DBAL\Types\RecordType;
use Ang3\Bundle\OdooApiBundle\ORM\Factory\ManagerFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Manager;
use Ang3\Bundle\OdooApiBundle\ORM\Registry;
use Ang3\Bundle\OdooApiBundle\ORM\Model as Models;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Joanis ROUANET
 */
class Ang3OdooApiExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
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
        $connections = $this->loadClients($container, $config['connections'], $config['default_connection']);

        // Si l'ORM est activé
        if (true === $config['orm']['enabled']) {
            // Chargement de l'ORM
            $this->loadOrm($container, $connections, $config['orm'], $config['default_connection']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // Odoo base models
        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    RecordType::NAME => [
                        'class' => RecordType::class,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Load clients instances from connections params.
     *
     * @param ContainerBuilder $container
     * @param array            $connections
     * @param string           $defaultConnection
     *
     * @return Reference[]
     */
    public function loadClients(ContainerBuilder $container, array $connections, string $defaultConnection)
    {
        // Si la connexion par défat n'existe pas
        if (!array_key_exists($defaultConnection, $connections)) {
            throw new InvalidArgumentException(sprintf('The default Odoo connection "%s" is not configured', $defaultConnection));
        }

        // Pour chaque conenctions
        foreach ($connections as $name => &$connection) {
            // Mise-à-jour de la connexion par la référence du client associé
            $connection = $this->createClient($container, $name, $connection, $name === $defaultConnection);
        }

        // Retour des conenctions
        return $connections;
    }

    /**
     * Load registry.
     *
     * @param ContainerBuilder $container
     * @param array            $connections
     * @param array            $orm
     * @param string           $defaultConnection
     */
    public function loadOrm(ContainerBuilder $container, array $connections, array $orm, string $defaultConnection)
    {
        // Récupération du registre des connections
        $registryDefinition = new Definition(Registry::class);

        // Enregistrement du client par défaut
        $registryDefinition->addArgument($defaultConnection);

        // Pour chaque client à créer
        foreach ($orm['managers'] as $managerName => $params) {
            // Création de la définition du manager
            $managerDefinition = new Definition(Manager::class);

            // Définition du nom du manager
            $managerServiceName = sprintf('ang3_odoo_api.%s.record_manager', $managerName);

            // Relevé du nom de la connexion du manager
            $connectionName = $params['connection'] ?: $defaultConnection;

            // Si la connection du manager n'existe pas
            if (!array_key_exists($connectionName, $connections)) {
                throw new InvalidArgumentException(sprintf('The connection "%s" of Odoo ORM manager "%s" is not configured', $params['connection'], $managerName));
            }

            // Enregistrement des arguments de la définition
            $managerDefinition
                ->setFactory([new Reference(ManagerFactory::class), 'create'])
                ->setArguments([$connections[$connectionName], $params['mapping'], $params['load_defaults']])
            ;

            // Enregistrement du manager dans le container
            $container->setDefinition($managerServiceName, $managerDefinition);

            // S'il s'agit du client par défaut
            if ($managerName === $defaultConnection) {
                // Enregistrement du registre de modèle par défaut dans le container
                $container->setAlias('ang3_odoo_api.record_manager', new Alias($managerServiceName, true));
                $container->setAlias(Manager::class, new Alias($managerServiceName, false));
            }

            // Enregistrement du client au sein du registre des connections
            $registryDefinition->addMethodCall('register', [$managerName, new Reference($managerServiceName)]);
        }

        // Enregistrement du registre des connections dans le container
        $container->setDefinition(Registry::class, $registryDefinition);
    }

    /**
     * Create client and returns its reference.
     *
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $params
     * @param bool             $isDefaultClient
     *
     * @return Reference
     */
    public function createClient(ContainerBuilder $container, string $name, array $params, bool $isDefaultClient)
    {
        // Création de la définition
        $definition = new Definition(ExternalApiClient::class);

        // Enregistrement des arguments de la définition
        $definition
            ->setFactory([new Reference(ApiClientFactory::class), 'create'])
            ->setArguments($params)
        ;

        // Définition du nom du client
        $clientName = sprintf('ang3_odoo_api.%s.client', $name);

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
