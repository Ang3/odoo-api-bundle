<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Catalog;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\TypeCollection;
use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Log\LoggerInterface;

/**
 * Service container for the record manager.
 *
 * @author Joanis ROUANET
 */
class Configuration
{
    /**
     * @var TypeCollection
     */
    private $types;

    /**
     * @var Catalog
     */
    private $catalog;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param TypeCollection       $types
     * @param Catalog              $catalog
     * @param ClassMetadataFactory $classMetadataFactory
     * @param Normalizer           $normalizer
     * @param LoggerInterface|null $logger
     */
    public function __construct(TypeCollection $types, Catalog $catalog, ClassMetadataFactory $classMetadataFactory, Normalizer $normalizer, LoggerInterface $logger = null)
    {
        $this->types = $types;
        $this->catalog = $catalog;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->normalizer = $normalizer;
        $this->logger = $logger;
    }

    /**
     * @static
     *
     * @param TypeCollection|null  $types
     * @param Catalog|null         $catalog
     * @param LoggerInterface|null $logger
     */
    public static function create(TypeCollection $types = null, Catalog $catalog = null, LoggerInterface $logger = null)
    {
        // Création de la configuration
        $config = new static();

        // Hydratation
        $config->types = $types ?: new TypeCollection();
        $config->catalog = $catalog ?: new Catalog();
        $config->classMetadataFactory = new ClassMetadataFactory(new AnnotationReader(), $config->getTypes());
        $config->normalizer = new Normalizer($config->getClassMetadataFactory());
        $config->logger = $logger;

        // Retour de la nouvelle configuration
        return $config;
    }

    /**
     * @return ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        return $this->classMetadataFactory;
    }

    /**
     * @return TypeCollection
     */
    public function getTypes()
    {
        return $this->types;
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
