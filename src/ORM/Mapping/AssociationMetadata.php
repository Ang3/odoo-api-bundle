<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionProperty;

/**
 * @author Joanis ROUANET
 */
class AssociationMetadata extends AbstractProperty
{
    /**
     * @var string
     */
    private $targetClass;

    /**
     * @param string             $name
     * @param string             $serializedName
     * @param ReflectionProperty $reflection
     * @param string             $targetClass
     */
    public function __construct(string $name, string $serializedName, ReflectionProperty $reflection, string $targetClass)
    {
        // Hydratation
        $this->targetClass = $targetClass;

        // Constructeur de la propriété de base
        parent::__construct($name, $serializedName, $reflection);
    }

    /**
     * @param string $targetClass
     *
     * @return self
     */
    public function setTargetClass(string $targetClass)
    {
        $this->targetClass = $targetClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }

    /**
     * {@inheritdoc}.
     */
    public function isField()
    {
        return false;
    }

    /**
     * {@inheritdoc}.
     */
    public function isAssociation()
    {
        return true;
    }
}
