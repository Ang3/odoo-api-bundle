<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Model("res.company")
 * 
 * @author Joanis ROUANET
 */
class Company extends Record
{
    use ContactTypeTrait;

    /**
     * @var Partner
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Partner'>")
     * @JMS\SerializedName("partner_id")
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
