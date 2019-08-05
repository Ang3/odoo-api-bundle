<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionProperty;

/**
 * @author Joanis ROUANET
 */
interface PropertyInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getSerializedName();

    /**
     * @return ReflectionProperty
     */
    public function getReflection();

    /**
     * @return bool
     */
    public function isField();

    /**
     * @return bool
     */
    public function isAssociation();
}
