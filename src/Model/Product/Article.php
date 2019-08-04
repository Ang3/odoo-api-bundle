<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Product;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\Model\Record;
use Ang3\Bundle\OdooApiBundle\Model\ActivatableRecordTrait;
use Ang3\Bundle\OdooApiBundle\Model\NamedRecordTrait;
use Ang3\Bundle\OdooApiBundle\Model\Res\Company;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class Article extends Record
{
    use ActivatableRecordTrait;
    use NamedRecordTrait;

    /**
     * @var Company
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne")
     * @JMS\SerializedName("company_id")
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Res\Company")
     */
    protected $company;

    /**
     * @param Company $company
     *
     * @return self
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }
}
