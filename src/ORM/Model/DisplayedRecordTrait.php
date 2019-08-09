<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model;

/**
 * @author Joanis ROUANET
 */
trait DisplayedRecordTrait
{
    /**
     * @var string
     *
     * @ORM\Field(name="display_name", type="string")
     */
    protected $displayName;

    /**
     * @param string $displayName
     *
     * @return self
     */
    public function setDisplayName(string $displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}
