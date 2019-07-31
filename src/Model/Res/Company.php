<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\Model\AbstractRecord;
use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 *
 * @Odoo\Model("res.company")
 */
class Company extends AbstractRecord
{
    use ContactTypeTrait;

    /**
     * @var ManyToOne
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\Model\ManyToOne")
     * @JMS\SerializedName("partner_id")
     *
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Res\Partner")
     */
    protected $partner;

    /**
     * @param ManyToOne $partner
     *
     * @return self
     */
    public function setPartner(ManyToOne $partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return ManyToOne
     */
    public function getPartner()
    {
        return $this->partner;
    }
}
