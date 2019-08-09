<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionClass;

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
     * @var ReflectionClass|null
     */
    private $reflectionClass;

    /**
     * @param string $localName
     * @param string $remoteName
     * @param string $targetClass
     * @param bool   $nullable
     */
    public function __construct(string $localName, string $remoteName, string $targetClass, bool $nullable = true)
    {
        // Construction de la propriété de base
        parent::__construct($localName, $remoteName, $nullable);

        // Hydratation
        $this->targetClass = $targetClass;
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
     * Construct new instance without constructor.
     * 
     * @return object
     */
    public function newInstance()
    {
        return $this
            ->getReflectionClass()
            ->newInstanceWithoutConstructor()
        ;
    }

    /**
     * Get the reflection of target class.
     * 
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflectionClass = $this->reflectionClass ?: new ReflectionClass($this->targetClass);
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
