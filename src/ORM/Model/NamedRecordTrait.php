<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model;

/**
 * @author Joanis ROUANET
 */
trait NamedRecordTrait
{
    use DisplayedRecordTrait;

    /**
     * @var string
     *
     * @ORM\Field(name="name", type="string")
     */
    protected $name;

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
