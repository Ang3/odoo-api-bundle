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
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $serializedName;

    /**
     * @var ReflectionProperty
     */
    private $reflection;

    /**
     * @param string             $name
     * @param string             $serializedName
     * @param ReflectionProperty $reflection
     */
    public function __construct(string $name, string $serializedName, ReflectionProperty $reflection)
    {
        // Hydratation
        $this->name = $name;
        $this->serializedName = $serializedName;
        $this->reflection = $reflection;

        // On rend accessible la propriété
        $this->reflection->setAccessible(true);
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
     * @param ReflectionProperty $reflection
     *
     * @return self
     */
    public function setReflection(ReflectionProperty $reflection)
    {
        $this->reflection = $reflection;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * {@inheritdoc}.
     */
    public function setValue(object $object, $value = null)
    {
        $this->reflection->setValue($object, $value);

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getValue(object $object)
    {
        return $this->reflection->getValue($object);
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
