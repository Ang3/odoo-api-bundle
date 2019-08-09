<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionProperty;

/**
 * @abstract
 *
 * @author Joanis ROUANET
 */
abstract class AbstractProperty implements PropertyInterface
{
    /**
     * @var ClassMetadata
     */
    private $classMetadata;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $serializedName;

    /**
     * @param ClassMetadata $classMetadata
     * @param string        $name
     * @param string        $serializedName
     */
    public function __construct(ClassMetadata $classMetadata, string $name, string $serializedName)
    {
        $this->classMetadata = $classMetadata;
        $this->name = $name;
        $this->serializedName = $serializedName;
    }

    /**
     * @param ClassMetadata $classMetadata
     *
     * @return self
     */
    public function setClassMetadata(ClassMetadata $classMetadata)
    {
        $this->classMetadata = $classMetadata;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getClassMetadata()
    {
        return $this->classMetadata;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $serializedName
     *
     * @return self
     */
    public function setSerializedName(string $serializedName)
    {
        $this->serializedName = $serializedName;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getSerializedName()
    {
        return $this->serializedName;
    }

    /**
     * Get the reflection of the property.
     *
     * @return ReflectionProperty
     */
    public function getReflectionProperty()
    {
        return new ReflectionProperty($this->classMetadata->getClass(), $this->name);
    }

    /**
     * {@inheritdoc}.
     */
    public function isField()
    {
        return true;
    }

    /**
     * {@inheritdoc}.
     */
    public function isAssociation()
    {
        return false;
    }
}
