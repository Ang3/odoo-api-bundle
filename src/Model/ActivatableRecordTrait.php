<?php

namespace Ang3\Bundle\OdooApiBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
trait ActivatableRecordTrait
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("active")
     */
    protected $active;

    /**
     * @param bool $active
     *
     * @return self
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }
}
