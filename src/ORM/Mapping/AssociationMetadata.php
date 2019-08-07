<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

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
     * @param string $class
     * @param string $name
     * @param string $targetClass
     */
    public function __construct(string $class, string $name, string $targetClass)
    {
        // Hydratation
        $this->targetClass = $targetClass;

        // Construction de la réflection
        parent::__construct($class, $name);
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
