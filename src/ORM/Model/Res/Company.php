<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class Company extends Record
{
    use ContactTypeTrait;

    /**
     * @var Partner
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne")
     * @JMS\SerializedName("partner_id")
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Partner")
     */
    protected $partner;

    /**
     * @param Partner $partner
     *
     * @return self
     */
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }
}
