<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;

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
     * @ORM\ManyToOne(name="partner_id", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Partner", nullable=false)
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
