<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;

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
     * @ORM\Field(name="code", type="string")
     */
    protected $code;

    /**
     * @var int|null
     *
     * @ORM\Field(name="phone_code", type="integer")
     */
    protected $phoneCode;

    /**
     * @var Currency|null
     *
     * @ORM\ManyToOne(name="currency_id", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Res\Currency")
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
     * @param Currency|null $currency
     *
     * @return self
     */
    public function setCurrency(Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Currency|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
