<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\NamedRecordTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
trait ContactTypeTrait
{
    use NamedRecordTrait;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("street")
     */
    protected $street;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("city")
     */
    protected $city;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("zip")
     */
    protected $zip;

    /**
     * @var ManyToOne
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\Model\ManyToOne")
     * @JMS\SerializedName("country_id")
     *
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Res\Country")
     */
    protected $country;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("phone")
     */
    protected $phone;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("mobile")
     */
    protected $mobile;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("email")
     */
    protected $email;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("website")
     */
    protected $website;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("is_company")
     */
    protected $company;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("customer")
     */
    protected $customer;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("supplier")
     */
    protected $supplier;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("employee")
     */
    protected $employee;

    /**
     * @param string|null $street
     *
     * @return self
     */
    public function setStreet($street = null)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string|null $city
     *
     * @return self
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string|null $zip
     *
     * @return self
     */
    public function setZip($zip = null)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param ManyToOne $country
     *
     * @return self
     */
    public function setCountry(ManyToOne $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return ManyToOne
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string|null $phone
     *
     * @return self
     */
    public function setPhone(string $phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string|null $mobile
     *
     * @return self
     */
    public function setMobile(string $mobile = null)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string|null $email
     *
     * @return self
     */
    public function setEmail(string $email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $website
     *
     * @return self
     */
    public function setWebsite(string $website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

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
