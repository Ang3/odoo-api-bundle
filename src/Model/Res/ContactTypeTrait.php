<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Model\NamedRecordTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
trait ContactTypeTrait
{
    use NamedRecordTrait;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_company")
     */
    private $company;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("customer")
     */
    private $customer;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("supplier")
     */
    private $supplier;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("employee")
     */
    private $employee;

    /**
     * @param bool $company
     *
     * @return self
     */
    public function setCompany(bool $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCompany()
    {
        return $this->company;
    }

    /**
     * @param bool $customer
     *
     * @return self
     */
    public function setCustomer(bool $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCustomer()
    {
        return $this->customer;
    }

    /**
     * @param bool $supplier
     *
     * @return self
     */
    public function setSupplier(bool $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param bool $employee
     *
     * @return self
     */
    public function setEmployee(bool $employee)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmployee()
    {
        return $this->employee;
    }
}
