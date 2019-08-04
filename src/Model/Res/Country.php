<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\Model\AbstractRecord;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class Country extends AbstractRecord
{
    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("code")
     */
    protected $code;

    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("phone_code")
     */
    protected $phoneCode;

    /**
     * @var Currency
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\Model\Res\Currency")
     * @JMS\SerializedName("currency_id")
     * @Odoo\ManyToOne("Ang3\Bundle\OdooApiBundle\Model\Res\Currency")
     */
    protected $currency;

    /**
     * @param string|null $code
     *
     * @return self
     */
    public function setCode($code = null)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int|null $phoneCode
     *
     * @return self
     */
    public function setPhoneCode($phoneCode = null)
    {
        $this->phoneCode = $phoneCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * @param Currency $currency
     *
     * @return self
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
