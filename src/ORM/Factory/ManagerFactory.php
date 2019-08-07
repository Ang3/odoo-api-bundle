<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Factory;

use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\RecordNormalizer;

/**
 * @author Joanis ROUANET
 */
class ManagerFactory
{
    /**
     * @var CatalogFactory
     */
    private $catalogFactory;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var RecordNormalizer
     */
    private $recordNormalizer;

    /**
     * Constructor of the factory.
     *
     * @param CatalogFactory       $catalogFactory
     * @param ClassMetadataFactory $classMetadataFactory
     * @param RecordNormalizer     $recordNormalizer
     */
    public function __construct(CatalogFactory $catalogFactory, ClassMetadataFactory $classMetadataFactory, RecordNormalizer $recordNormalizer)
    {
        $this->catalogFactory = $catalogFactory;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->recordNormalizer = $recordNormalizer;
    }

    /**
     * Create a new record manager.
     *
     * @param ExternalApiClient $client
     * @param array             $mapping
     * @param bool              $loadDefaults
     *
     * @return RecordManager
     */
    public function create(ExternalApiClient $client, array $mapping = [], bool $loadDefaults = true)
    {
        // Création du catalogue de modèles
        $catalog = $this->catalogFactory->create($mapping, $loadDefaults);

        // Retour de la construction du manager
        return new RecordManager($client, $this->classMetadataFactory, $catalog, $this->recordNormalizer);
    }
}
