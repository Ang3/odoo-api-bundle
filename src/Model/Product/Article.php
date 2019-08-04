<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Product;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\Model\Record;
use Ang3\Bundle\OdooApiBundle\Model\ActivatableRecordTrait;
use Ang3\Bundle\OdooApiBundle\Model\NamedRecordTrait;
use Ang3\Bundle\OdooApiBundle\Model\Account\Tax;
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
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("type")
     */
    protected $type;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("default_code")
     */
    protected $defaultCode;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("barcode")
     */
    protected $barCode;

    /**
     * @var Tax[]
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToMany")
     * @JMS\SerializedName("taxes_id")
     * @Odoo\ManyToMany("Ang3\Bundle\OdooApiBundle\Model\Account\Tax")
     */
    protected $taxes = [];

    /**
     * @var Category
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne")
     * @JMS\SerializedName("categ_id")
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Product\Category")
     */
    protected $category;

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

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $defaultCode
     *
     * @return self
     */
    public function setDefaultCode(string $defaultCode = null)
    {
        $this->defaultCode = $defaultCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefaultCode()
    {
        return $this->defaultCode;
    }

    /**
     * @param string|null $barCode
     *
     * @return self
     */
    public function setBarCode(string $barCode = null)
    {
        $this->barCode = $barCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBarCode()
    {
        return $this->barCode;
    }

    /**
     * @param Tax[] $taxes
     *
     * @return self
     */
    public function setTaxes(array $taxes = [])
    {
        $this->taxes = $taxes;

        return $this;
    }

    /**
     * @param Tax $tax
     *
     * @return self
     */
    public function addTax(Tax $tax)
    {
        $this->taxes[] = $tax;

        return $this;
    }

    /**
     * @return Tax[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @param Category $category
     *
     * @return self
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
