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
     * Set value of an instance.
     *
     * @param object     $object
     * @param mixed|null $value
     *
     * @return self
     */
    public function setValue(object $object, $value = null);

    /**
     * Get value of an instance.
     *
     * @param object $object
     *
     * @return mixed|null
     */
    public function getValue(object $object);

    /**
     * @return bool
     */
    public function isField();

    /**
     * @return bool
     */
    public function isAssociation();
}
