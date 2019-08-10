<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Factory;

use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\ORM\Configuration;
use Ang3\Bundle\OdooApiBundle\ORM\Manager;
use Ang3\Bundle\OdooApiBundle\ORM\Normalizer;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\TypeCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Joanis ROUANET
 */
class ManagerFactory
{
    /**
     * @var TypeCollection
     */
    private $typeCollection;

    /**
     * @var CatalogFactory
     */
    private $catalogFactory;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Constructor of the factory.
     *
     * @param TypeCollection           $typeCollection
     * @param CatalogFactory           $catalogFactory
     * @param ClassMetadataFactory     $classMetadataFactory
     * @param Normalizer               $normalizer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TypeCollection $typeCollection, CatalogFactory $catalogFactory, ClassMetadataFactory $classMetadataFactory, Normalizer $normalizer, EventDispatcherInterface $eventDispatcher)
    {
        $this->typeCollection = $typeCollection;
        $this->catalogFactory = $catalogFactory;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->normalizer = $normalizer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create a new record manager.
     *
     * @param ExternalApiClient $client
     * @param array             $mapping
     * @param bool              $loadDefaults
     *
     * @return Manager
     */
    public function create(ExternalApiClient $client, array $mapping = [], bool $loadDefaults = true)
    {
        // Création du catalogue de modèles
        $catalog = $this->catalogFactory->create($mapping, $loadDefaults);

        // Création de la configuration
        $configuration = new Configuration($this->typeCollection, $catalog, $this->classMetadataFactory, $this->normalizer);

        // Retour de la construction du manager
        return new Manager($client, $configuration, $this->eventDispatcher);
    }
}
