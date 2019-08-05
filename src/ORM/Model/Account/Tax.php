<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Account;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\ActivatableRecordTrait;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Company;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class Tax extends Record
{
    use ActivatableRecordTrait;
    use NamedRecordTrait;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("description")
     */
    protected $description;

    /**
     * @var float
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("amount")
     */
    protected $amount;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("amount_type")
     */
    protected $amountType;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("type_taxe_use")
     */
    protected $scope;

    /**
     * @var bool
     *
     * @JMS\Type("bool")
     * @JMS\SerializedName("price_include")
     */
    protected $includedInPrice = false;

    /**
     * @var bool
     *
     * @JMS\Type("bool")
     * @JMS\SerializedName("inlude_base_amount")
     */
    protected $baseAmountIncluded = false;

    /**
     * @var TaxGroup
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne")
     * @JMS\SerializedName("company_id")
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\ORM\Model\Account\TaxGroup")
     */
    protected $group;

    /**
     * @var Company
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne")
     * @JMS\SerializedName("company_id")
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Company")
     */
    protected $company;

    /**
     * @param string|null $description
     *
     * @return self
     */
    public function setDescription(string $description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param float $amount
     *
     * @return self
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amountType
     *
     * @return self
     */
    public function setAmountType(string $amountType)
    {
        $this->amountType = $amountType;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmountType()
    {
        return $this->amountType;
    }

    /**
     * @param string $scope
     *
     * @return self
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param bool $includedInPrice
     *
     * @return self
     */
    public function setIncludedInPrice(bool $includedInPrice)
    {
        $this->includedInPrice = $includedInPrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludedInPrice()
    {
        return $this->includedInPrice;
    }

    /**
     * @param bool $baseAmountIncluded
     *
     * @return self
     */
    public function setBaseAmountIncluded(bool $baseAmountIncluded)
    {
        $this->baseAmountIncluded = $baseAmountIncluded;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBaseAmountIncluded()
    {
        return $this->baseAmountIncluded;
    }

    /**
     * @param TaxGroup $group
     *
     * @return self
     */
    public function setGroup(TaxGroup $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return TaxGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

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
