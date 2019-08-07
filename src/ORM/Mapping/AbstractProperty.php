<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionProperty;

/**
 * @abstract
 *
 * @author Joanis ROUANET
 */
abstract class AbstractProperty extends ReflectionProperty implements PropertyInterface
{
    /**
     * @var string
     */
    private $serializedName;

    /**
     * @param string $class
     * @param string $name
     */
    public function __construct(string $class, string $name)
    {
        // Hydratation
        $this->name = $name;

        // Construction de la réflection
        parent::__construct($class, $name);

        // On rend accessible la propriété
        $this->setAccessible(true);
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
     * {@inheritdoc}.
     */
    public function setValue($object, $value = null)
    {
        parent::setValue($object, $value);

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getValue($object = null)
    {
        return $object ? parent::getValue($object) : null;
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
