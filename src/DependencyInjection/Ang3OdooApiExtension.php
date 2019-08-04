<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\Client\Registry;
use Ang3\Bundle\OdooApiBundle\ORM\ModelRegistry;
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

        // Récupération du registre des clients
        $clientRegsitry = $container->getDefinition(Registry::class);

        // Pour chaque client à créer
        foreach($config['clients'] as $name => $params) {
            // Création de la définition
            $client = new Definition(ExternalApiClient::class);

            // Enregistrement des arguments de la définition
            $client->setArguments([ $params['url'], $params['database'], $params['username'], $params['password'] ]);

            // Définition du nom du client
            $clientName = sprintf('ang3_odoo_api.%s.client', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($clientName, $client);

            // S'il s'agit du client par défaut
            if($name === $config['default_client']) {
                // Enregistrement du client par défaut dans le container
                $container->setDefinition('ang3_odoo_api.client', $client);
                $container->setDefinition(ExternalApiClient::class, $client);
            }

            // Enregistrement du client au sein du registre des clients
            $clientRegsitry->addMethodCall('register', [$name, new Reference($clientName)]);

            // Création de la définition
            $modelRegistry = new Definition(ModelRegistry::class);

            // Ajout en argument des modèles du client
            $modelRegistry->addArgument($params['models']);

            // Définition du nom du client
            $modelRegistryName = sprintf('ang3_odoo_api.%s.model_registry', $name);

            // Enregistrement du client dans le container
            $container->setDefinition($modelRegistryName, $modelRegistry);

            // S'il s'agit du client par défaut
            if($name === $config['default_client']) {
                // Enregistrement du registre de modèle par défaut dans le container
                $container->setDefinition('ang3_odoo_api.model_registry', $modelRegistry);
                $container->setDefinition(ModelRegistry::class, $modelRegistry);
            }
        }

        // Chargement des services
        $loader->load('services.yml');
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
}
