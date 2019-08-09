<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

/**
 * @author Joanis ROUANET
 */
class ManyToOneMetadata extends AssociationMetadata
{
    /**
     * @var bool
     */
    private $nullable;

    /**
     * @param string $localName
     * @param string $remoteName
     * @param string $targetClass
     * @param bool   $nullable
     */
    public function __construct(string $localName, string $remoteName, string $targetClass, bool $nullable = true)
    {
        // Construction de la propriété de base
        parent::__construct($localName, $remoteName, $targetClass);

        // Hydratation
        $this->nullable = $nullable;
    }

    /**
     * @param bool $nullable
     *
     * @return self
     */
    public function setNullable(bool $nullable)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function isNullable()
    {
        return $this->nullable;
    }
}
