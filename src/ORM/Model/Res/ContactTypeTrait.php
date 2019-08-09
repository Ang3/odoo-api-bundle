<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;

/**
 * @author Joanis ROUANET
 */
trait ContactTypeTrait
{
    use NamedRecordTrait;

    /**
     * @var string|null
     *
     * @ORM\Field(name="street", type="string")
     */
    protected $street;

    /**
     * @var string|null
     *
     * @ORM\Field(name="city", type="string")
     */
    protected $city;

    /**
     * @var string|null
     *
     * @ORM\Field(name="zip", type="string")
     */
    protected $zip;

    /**
     * @var string|null
     *
     * @ORM\Field(name="phone", type="string")
     */
    protected $phone;

    /**
     * @var string|null
     *
     * @ORM\Field(name="mobile", type="string")
     */
    protected $mobile;

    /**
     * @var string|null
     *
     * @ORM\Field(name="email", type="string")
     */
    protected $email;

    /**
     * @var string|null
     *
     * @ORM\Field(name="website", type="string")
     */
    protected $website;

    /**
     * @var bool
     *
     * @ORM\Field(name="is_company", type="boolean")
     */
    protected $company = false;

    /**
     * @var bool
     *
     * @ORM\Field(name="customer", type="boolean")
     */
    protected $customer = false;

    /**
     * @var bool
     *
     * @ORM\Field(name="supplier", type="boolean")
     */
    protected $supplier = false;

    /**
     * @var bool
     *
     * @ORM\Field(name="employee", type="boolean")
     */
    protected $employee = false;

    /**
     * @var Country|null
     *
     * @ORM\ManyToOne(name="country_id", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Country")
     */
    protected $country;

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

    /**
     * @param Country|null $country
     *
     * @return self
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }
}
