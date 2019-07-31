<?php

namespace Ang3\Bundle\OdooApiBundle\Model;

use DateTime;
use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use JMS\Serializer\Annotation as JMS;

/**
 * @abstract
 *
 * @author Joanis ROUANET
 */
abstract class AbstractRecord implements RecordInterface
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
     * @JMS\Exclude(if="context.getDirection() == constant('JMS\\Serializer\\GraphNavigator::DIRECTION_SERIALIZATION')")
     */
    protected $createdAt;

    /**
     * @var ManyToOne
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\Model\ManyToOne")
     * @JMS\SerializedName("create_uid")
     * @JMS\Exclude(if="context.getDirection() == constant('JMS\\Serializer\\GraphNavigator::DIRECTION_SERIALIZATION')")
     *
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Res\User")
     */
    protected $createdBy;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\SerializedName("write_date")
     * @JMS\Exclude(if="context.getDirection() == constant('JMS\\Serializer\\GraphNavigator::DIRECTION_SERIALIZATION')")
     */
    protected $updatedAt;

    /**
     * @var ManyToOne
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\Model\ManyToOne")
     * @JMS\SerializedName("write_uid")
     * @JMS\Exclude(if="context.getDirection() == constant('JMS\\Serializer\\GraphNavigator::DIRECTION_SERIALIZATION')")
     *
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Res\User")
     */
    protected $updatedBy;

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
     * @param ManyToOne $createdBy
     *
     * @return self
     */
    public function setCreatedBy(ManyToOne $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return ManyToOne
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
     * @param ManyToOne $updatedBy
     *
     * @return self
     */
    public function setUpdatedBy(ManyToOne $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return ManyToOne
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
