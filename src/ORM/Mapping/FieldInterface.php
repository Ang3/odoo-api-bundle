<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

/**
 * @author Joanis ROUANET
 */
interface FieldInterface
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
     * @return bool
     */
    public function isField();

    /**
     * @return bool
     */
    public function isAssociation();
}
