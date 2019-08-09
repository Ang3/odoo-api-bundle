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
     * @param ClassMetadata $classMetadata
     * @param string        $name
     * @param string        $serializedName
     * @param string        $targetClass
     */
    public function __construct(ClassMetadata $classMetadata, string $name, string $serializedName, string $targetClass)
    {
        // Construction de la ropriété
        parent::__construct($classMetadata, $name, $serializedName);

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
