<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model;

/**
 * @author Joanis ROUANET
 */
interface RecordInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getDisplayName();
}
