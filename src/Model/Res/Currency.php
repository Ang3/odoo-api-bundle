<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Annotations as Odoo;
use Ang3\Bundle\OdooApiBundle\Model\AbstractRecord;
use Ang3\Bundle\OdooApiBundle\Model\ActivatableModelTrait;
use Ang3\Bundle\OdooApiBundle\Model\NamedRecordTrait;

/**
 * @author Joanis ROUANET
 *
 * @Odoo\Model("res.partner")
 */
class Currency extends AbstractRecord
{
    use ActivatableModelTrait;
    use NamedRecordTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("symbol")
     */
    private $symbol;

    /**
     * @var float
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("rounding")
     */
    private $rounding;

    /**
     * @var float
     *
     * @JMS\Type("float")
     * @JMS\SerializedName("rate")
     */
    private $rate;

    /**
     * Constructor of the record.
     *
     * @param string $displayName
     * @param string $name
     * @param string $symbol
     */
    public function __construct($displayName, $name = null, $symbol)
    {
        // Hydratation
        $this->displayName = $displayName;
        $this->name = $name ?: $displayName;
        $this->symbol = $symbol;
    }

    /**
     * @param string $symbol
     *
     * @return self
     */
    public function setSymbol(string $symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param float $rounding
     *
     * @return self
     */
    public function setRounding(float $rounding)
    {
        $this->rounding = $rounding;

        return $this;
    }

    /**
     * @return float
     */
    public function getRounding()
    {
        return $this->rounding;
    }

    /**
     * @param float $rate
     *
     * @return self
     */
    public function setRate(float $rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }
}
