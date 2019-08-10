<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types\ArrayType;

/**
 * @author Joanis ROUANET
 */
class OneToManyMetadata extends AssociationMetadata
{
    /**
     * @param string $localName
     * @param string $remoteName
     * @param string $targetClass
     * @param array  $options
     */
    public function __construct(string $localName, string $remoteName, string $targetClass, array $options = [])
    {
        // Construction des métadonnées de base
        parent::__construct($localName, $remoteName, new ArrayType(), $targetClass, $options);
    }
}
