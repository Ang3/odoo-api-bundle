<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model;

use DateTime;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Res\User;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class Record implements RecordInterface
{
    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("id")
     */
    protected $id;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\SerializedName("create_date")
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Res\User'>")
     * @JMS\SerializedName("create_uid")
     */
    protected $createdBy;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\SerializedName("write_date")
     */
    protected $updatedAt;

    /**
     * @var User
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Res\User'>")
     * @JMS\SerializedName("write_uid")
     */
    protected $updatedBy;

    /**
     * @var bool
     *
     * @JMS\Exclude
     */
    protected $__loaded = false;

    /**
     * {@inherited}.
     */
    public function setId(int $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inherited}.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param User $createdBy
     *
     * @return self
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param DateTime $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param User $updatedBy
     *
     * @return self
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return string|null
     */
    public function getDisplayName()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return $this->__loaded;
    }
}
