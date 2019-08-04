<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\Client\Registry;
use Ang3\Bundle\OdooApiBundle\ORM\ModelRegistry;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\ORM\RecordNormalizer;
use Ang3\Bundle\OdooApiBundle\Model as Models;
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

        // Chargement du registre des clients
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
     * Load clients and ORM.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function loadRegistry(ContainerBuilder $container, array $config)
    {
        // Récupération mapping par défaut
        $defaultMapping = $this->getDefaultMapping();

        // Récupération du registre des clients
        $registryDefinition = new Definition(Registry::class);

        // Pour chaque client à créer
        foreach ($config['clients'] as $name => $params) {
            // Création de la définition
            $client = new Definition(ExternalApiClient::class);

            // Enregistrement des arguments de la définition
            $client->setArguments([$params['url'], $params['database'], $params['username'], $params['password']]);

            // Définition du nom du client
            $clientName = sprintf('ang3_odoo_api.%s.client', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($clientName, $client);

            // Enregistrement du client au sein du registre des clients
            $registryDefinition->addMethodCall('register', [$name, new Reference($clientName)]);

            // Création de la définition du registre des modèles
            $modelRegistry = new Definition(ModelRegistry::class);

            // Initialisation du mapping
            $mapping = array_merge($config['mapping'], $params['mapping']);

            // Si on souhaite charger le mapping par défaut
            if (true === $params['defaults']) {
                // On charge le mapping par défaut en mode non prioritaire
                $mapping = array_merge($defaultMapping, $mapping);
            }

            // Ajout en argument des modèles du client
            $modelRegistry->addArgument($mapping);

            // Définition du nom du client
            $modelRegistryName = sprintf('ang3_odoo_api.%s.model_registry', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($modelRegistryName, $modelRegistry);

            // Création de la définition du manager
            $manager = new Definition(RecordManager::class);

            // Définition du nom du manager
            $managerName = sprintf('ang3_odoo_api.%s.record_manager', $name);

            // Enregistrement des arguments de la définition
            $manager->setArguments([new Reference($clientName), new Reference($modelRegistryName), new Reference(RecordNormalizer::class)]);

            // Enregistrement du manager dans le container
            $container->setDefinition($managerName, $manager);

            // S'il s'agit du client par défaut
            if ($name === $config['default_client']) {
                // Enregistrement du client par défaut dans le container
                $container->setDefinition('ang3_odoo_api.client', $client);
                $container->setDefinition(ExternalApiClient::class, $client);

                // Enregistrement du registre de modèle par défaut dans le container
                $container->setDefinition('ang3_odoo_api.model_registry', $modelRegistry);
                $container->setDefinition(ModelRegistry::class, $modelRegistry);

                // Enregistrement du registre de modèle par défaut dans le container
                $container->setDefinition('ang3_odoo_api.record_manager', $manager);
                $container->setDefinition(RecordManager::class, $manager);
            }
        }

        // Enregistrement du registre des clients dans le container
        $container->setDefinition(Registry::class, $registryDefinition);
    }

    /**
     * Get default mapping.
     *
     * @return array
     */
    public function getDefaultMapping()
    {
        return [
            'res.user' => Models\Res\User::class,
            'res.company' => Models\Res\Company::class,
            'res.partner' => Models\Res\Partner::class,
            'res.country' => Models\Res\Country::class,
            'res.currency' => Models\Res\Currency::class,
            'product.template' => Models\Product\Article::class,
        ];
    }
}
