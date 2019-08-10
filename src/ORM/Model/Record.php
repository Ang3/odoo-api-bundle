<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model;

use DateTime;
use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Res\User;

/**
 * @author Joanis ROUANET
 */
class Record implements RecordInterface
{
    /**
     * @var int|null
     *
     * @ORM\ReadOnly
     * @ORM\Field(name="id", type="integer")
     */
    protected $id;

    /**
     * @var DateTime
     *
     * @ORM\ReadOnly
     * @ORM\Field(name="create_date", type="datetime")
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @ORM\ReadOnly
     * @ORM\ManyToOne(name="create_uid", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Res\User")
     */
    protected $createdBy;

    /**
     * @var DateTime
     *
     * @ORM\ReadOnly
     * @ORM\Field(name="write_date", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var User
     *
     * @ORM\ReadOnly
     * @ORM\ManyToOne(name="write_uid", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Res\User")
     */
    protected $updatedBy;

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
}
