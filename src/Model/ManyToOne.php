<?php

namespace Ang3\Bundle\OdooApiBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class ManyToOne
{
    use DisplayedRecordTrait;

    /**
     * @var string
     *
     * @JMS\Exclude
     */
    protected $target;

    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("id")
     */
    protected $id;

    /**
     * @param string      $target
     * @param int|null    $id
     * @param string|null $displayName
     */
    public function __construct(string $target, int $id = null, $displayName = null)
    {
        $this->target = $target;
        $this->id = $id;
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Serialize the association.
     *
     * @return array
     */
    public function serialize()
    {
        return [$this->id, $this->displayName];
    }

    /**
     * Check if the association is loadable.
     *
     * @return bool
     */
    public function isLoadable()
    {
        return null !== $this->target && null !== $this->id;
    }
}
