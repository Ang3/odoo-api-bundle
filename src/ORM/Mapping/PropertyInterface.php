<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

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
