<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Product;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\ActivatableRecordTrait;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Account\Tax;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Company;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Model("product.template")
 *
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
     * @JMS\SerializedName("description")
     */
    protected $description;

    /**
     * @var float|null
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("list_price")
     */
    protected $sellingPrice;

    /**
     * @var float|null
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("standard_price")
     */
    protected $cost;

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
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\MultipleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Account\Tax'>")
     * @JMS\SerializedName("taxes_id")
     */
    protected $taxes = [];

    /**
     * @var Tax[]
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\MultipleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Account\Tax'>")
     * @JMS\SerializedName("supplier_taxes_id")
     */
    protected $supplierTaxes = [];

    /**
     * @var Category
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Product\Category'>")
     * @JMS\SerializedName("categ_id")
     */
    protected $category;

    /**
     * @var Company
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Company'>")
     * @JMS\SerializedName("company_id")
     */
    protected $company;

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
     * @param float|null $sellingPrice
     *
     * @return self
     */
    public function setSellingPrice(float $sellingPrice = null)
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getSellingPrice()
    {
        return $this->sellingPrice;
    }

    /**
     * @param float|null $cost
     *
     * @return self
     */
    public function setCost(float $cost = null)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCost()
    {
        return $this->cost;
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
     * @param Tax[] $supplierTaxes
     *
     * @return self
     */
    public function setSupplierTaxes(array $supplierTaxes = [])
    {
        $this->supplierTaxes = $supplierTaxes;

        return $this;
    }

    /**
     * @param Tax $supplierTaxes
     *
     * @return self
     */
    public function addSupplierTax(Tax $supplierTaxes)
    {
        $this->supplierTaxes[] = $supplierTaxes;

        return $this;
    }

    /**
     * @return Tax[]
     */
    public function getSupplierTaxes()
    {
        return $this->supplierTaxes;
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
