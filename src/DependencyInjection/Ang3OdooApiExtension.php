<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Component\OdooApiClient\Factory\ApiClientFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Factory\CatalogFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Catalog;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\ORM\RecordNormalizer;
use Ang3\Bundle\OdooApiBundle\ORM\Registry;
use Ang3\Bundle\OdooApiBundle\ORM\Model as Models;
use Ang3\Bundle\OdooApiBundle\DBAL\Types\RecordType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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

        // Chargement du registre des connections
        $this->loadRegistry($container, $config);
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
     * Load registry.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function loadRegistry(ContainerBuilder $container, array $config)
    {
        // Création de l'usine des catalogues et récupération de sa référence
        $catalogFactory = new Reference(CatalogFactory::class);

        // Récupération du registre des connections
        $registryDefinition = new Definition(Registry::class);

        // Enregistrement du client par défaut
        $registryDefinition->addArgument($config['default_connection']);

        // Pour chaque client à créer
        foreach ($config['connections'] as $name => $params) {
            // Création de la définition
            $clientDefinition = new Definition(ExternalApiClient::class);

            // Enregistrement des arguments de la définition
            $clientDefinition->setArguments([$params['url'], $params['database'], $params['user'], $params['password']]);

            // Définition du nom du client
            $clientName = sprintf('ang3_odoo_api.%s.client', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($clientName, $clientDefinition);

            // Création de la définition du registre des modèles
            $catalogDefinition = new Definition(Catalog::class);

            // Ajout en argument des modèles du client
            $catalogDefinition
                ->setFactory([$catalogFactory, 'create'])
                ->setArguments([$params['mapping'], true === $params['defaults']])
            ;

            // Définition du nom du client
            $catalogName = sprintf('ang3_odoo_api.%s.catalog', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($catalogName, $catalogDefinition);

            // Création de la définition du manager
            $managerDefinition = new Definition(RecordManager::class);

            // Définition du nom du manager
            $managerName = sprintf('ang3_odoo_api.%s.record_manager', $name);

            // Enregistrement des arguments de la définition
            $managerDefinition->setArguments([new Reference($clientName), new Reference($catalogName), new Reference(RecordNormalizer::class)]);

            // Enregistrement du manager dans le container
            $container->setDefinition($managerName, $managerDefinition);

            // S'il s'agit du client par défaut
            if ($name === $config['default_connection']) {
                // Enregistrement du client par défaut dans le container
                $container->setDefinition('ang3_odoo_api.client', $clientDefinition);
                $container->setDefinition(ExternalApiClient::class, $clientDefinition);

                // Enregistrement du registre de modèle par défaut dans le container
                $container->setDefinition('ang3_odoo_api.catalog', $catalogDefinition);
                $container->setDefinition(Catalog::class, $catalogDefinition);

                // Enregistrement du registre de modèle par défaut dans le container
                $container->setDefinition('ang3_odoo_api.record_manager', $managerDefinition);
                $container->setDefinition(RecordManager::class, $managerDefinition);
            }

            // Enregistrement du client au sein du registre des connections
            $registryDefinition->addMethodCall('register', [$name, new Reference($managerName)]);
        }

        // Enregistrement du registre des connections dans le container
        $container->setDefinition(Registry::class, $registryDefinition);
    }

    /**
     * Create client and returns its reference.
     * 
     * @param  ContainerBuilder $container
     * @param  string           $name
     * @param  array            $params
     * @param  bool             $isDefaultClient
     * 
     * @return Reference
     */
    public function createClient(ContainerBuilder $container, string $name, array $params, bool $isDefaultClient)
    {
        // Création de la définition
        $definition = new Definition(ExternalApiClient::class);

        // Enregistrement des arguments de la définition
        $definition
            ->setFactory(new Reference(ApiClientFactory::class))
            ->setArguments($params)
        ;

        // Définition du nom du client
        $clientName = sprintf('ang3_odoo_api.%s.client', $name);

        // Enregistrement du client dans le container
        $container->setDefinition($clientName, $definition);

        // S'il s'agit du client par défaut
        if(true === $isDefaultClient) {
            // Enregistrement du client par défaut
            $container
                ->setAlias('ang3_odoo_api.client', $clientName)
                ->setPublic(true)
            ;

            // Enregistrement du client par défaut
            $container
                ->setAlias(ExternalApiClient::class, $clientName)
                ->setPublic(true)
            ;
        }

        // Retour de la référence du service client
        return new Reference($clientName);
    }
}
