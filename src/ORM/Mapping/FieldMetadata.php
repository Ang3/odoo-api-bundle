<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types\TypeInterface;

/**
 * @author Joanis ROUANET
 */
class FieldMetadata extends AbstractProperty
{
    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * @param string        $localName
     * @param string        $remoteName
     * @param TypeInterface $type
     * @param bool          $nullable
     */
    public function __construct(string $localName, string $remoteName, TypeInterface $type, bool $nullable = true)
    {
        // Construction de la propriété de base
        parent::__construct($localName, $remoteName);

        // Hydratation
        $this->type = $type;
        $this->nullable = $nullable;
    }

    /**
     * @param TypeInterface $type
     *
     * @return self
     */
    public function setType(TypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return TypeInterface
     */
    public function getType()
    {
        return $this->type;
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
