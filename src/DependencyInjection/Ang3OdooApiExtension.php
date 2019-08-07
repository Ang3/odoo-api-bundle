<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Component\OdooApiClient\ExternalApiClient;
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
        // Récupération mapping par défaut
        $defaultMapping = $this->getDefaultMapping();

        // Récupération du registre des connections
        $registryDefinition = new Definition(Registry::class);

        // Enregistrement du client par défaut
        $registryDefinition->addArgument($config['default_connection']);

        // Pour chaque client à créer
        foreach ($config['connections'] as $name => $params) {
            // Création de la définition
            $clientDefinition = new Definition(ExternalApiClient::class);

            // Enregistrement des arguments de la définition
            $clientDefinition->setArguments([$params['url'], $params['database'], $params['username'], $params['password']]);

            // Définition du nom du client
            $clientName = sprintf('ang3_odoo_api.%s.client', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($clientName, $clientDefinition);

            // Création de la définition du registre des modèles
            $catalogDefinition = new Definition(Catalog::class);

            // Initialisation du mapping
            $mapping = array_merge($config['mapping'], $params['mapping']);

            // Si on souhaite charger le mapping par défaut
            if (true === $params['defaults']) {
                // On charge le mapping par défaut en mode non prioritaire
                $mapping = array_merge($defaultMapping, $mapping);
            }

            // Ajout en argument des modèles du client
            $catalogDefinition->addArgument($mapping);

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
            'product.category' => Models\Product\Category::class,
            'account.tax' => Models\Account\Tax::class,
            'account.tax.group' => Models\Account\TaxGroup::class,
        ];
    }
}
