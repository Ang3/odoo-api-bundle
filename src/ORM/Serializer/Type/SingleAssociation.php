<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type;

/**
 * @author Joanis ROUANET
 */
class SingleAssociation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @param int         $id
     * @param string|null $displayName
     */
    public function __construct(int $id, string $displayName = null)
    {
        $this->id = $id;
        $this->displayName = $displayName;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $displayName
     *
     * @return self
     */
    public function setDisplayName(string $displayName = null)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
}
