<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types\TypeInterface;

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
     * @param string        $localName
     * @param string        $remoteName
     * @param TypeInterface $type
     * @param string        $targetClass
     * @param array         $options
     */
    public function __construct(string $localName, string $remoteName, TypeInterface $type, string $targetClass, array $options = [])
    {
        // Construction de la propriété de base
        parent::__construct($localName, $remoteName, $type, $options);

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
