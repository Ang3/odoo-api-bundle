<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionProperty;

/**
 * @abstract
 *
 * @author Joanis ROUANET
 */
abstract class AbstractField extends ReflectionProperty implements FieldInterface
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
