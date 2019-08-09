<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

/**
 * @author Joanis ROUANET
 */
class FieldMetadata extends AbstractProperty
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $localName
     * @param string $remoteName
     * @param string $type
     * @param bool   $nullable
     */
    public function __construct(string $localName, string $remoteName, string $type, bool $nullable = true)
    {
        // Construction de la propriété de base
        parent::__construct($localName, $remoteName, $nullable);

        // Hydratation
        $this->type = $type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
