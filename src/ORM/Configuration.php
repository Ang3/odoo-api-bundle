<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Catalog;
use Psr\Log\LoggerInterface;

/**
 * Service container for the record manager.
 *
 * @author Joanis ROUANET
 */
class Configuration
{
    /**
     * @var Catalog
     */
    private $catalog;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param Catalog              $catalog
     * @param Normalizer           $normalizer
     * @param ClassMetadataFactory $classMetadataFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(Catalog $catalog, Normalizer $normalizer, ClassMetadataFactory $classMetadataFactory, LoggerInterface $logger = null)
    {
        $this->catalog = $catalog;
        $this->normalizer = $normalizer;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->logger = $logger;
    }

    /**
     * @return ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        return $this->classMetadataFactory;
    }

    /**
     * @return Catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * @return Normalizer
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
