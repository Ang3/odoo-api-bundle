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
     * @param string        $localName
     * @param string        $remoteName
     * @param TypeInterface $type
     * @param array         $options
     */
    public function __construct(string $localName, string $remoteName, TypeInterface $type, array $options = [])
    {
        // Construction de la propriété de base
        parent::__construct($localName, $remoteName, $options);

        // Hydratation
        $this->type = $type;
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
}
