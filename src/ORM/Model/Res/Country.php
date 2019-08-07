<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Model("res.country")
 *
 * @author Joanis ROUANET
 */
class Country extends Record
{
    use NamedRecordTrait;

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
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Currency'>")
     * @JMS\SerializedName("currency_id")
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
