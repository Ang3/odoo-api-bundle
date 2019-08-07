<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model;

/**
 * @author Joanis ROUANET
 */
interface RecordInterface
{
    /**
     * @param int|null $id
     *
     * @return self
     */
    public function setId(int $id = null);

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getDisplayName();
}
