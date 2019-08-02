<?php

namespace Ang3\Bundle\OdooApiBundle\DependencyInjection;

use Ang3\Bundle\OdooApiBundle\DBAL\Types\RecordType;
use Ang3\Bundle\OdooApiBundle\Model\Res\Company;
use Ang3\Bundle\OdooApiBundle\Model\Res\Country;
use Ang3\Bundle\OdooApiBundle\Model\Res\Currency;
use Ang3\Bundle\OdooApiBundle\Model\Res\Partner;
use Ang3\Bundle\OdooApiBundle\Model\Res\User;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $container->setParameter('ang3_odoo_api.models', $config['models']);

        // Définition d'un chargeur de fichier YAML
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Chargement des services
        $loader->load('services.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        // Doctrine types
        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    'RecordType' => [
                        'class' => RecordType::class,
                    ],
                ],
            ],
        ]);

        // Odoo base models
        $container->prependExtensionConfig('ang3_odoo_api', [
            'models' => [
                Company::class,
                Country::class,
                Currency::class,
                Partner::class,
                User::class,
            ],
        ]);
    }
}
