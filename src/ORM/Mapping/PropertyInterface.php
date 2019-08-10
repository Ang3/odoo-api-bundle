<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types\TypeInterface;

/**
 * @author Joanis ROUANET
 */
interface PropertyInterface
{
    /**
     * @return string
     */
    public function getLocalName();

    /**
     * @return string
     */
    public function getRemoteName();

    /**
     * @return TypeInterface
     */
    public function getType();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * Check if the property is in read only mode.
     *
     * @return bool
     */
    public function isReadOnly();

    /**
     * Check if the property is nullable.
     *
     * @return bool
     */
    public function isNullable();

    /**
     * @return bool
     */
    public function isField();

    /**
     * @return bool
     */
    public function isAssociation();
}
