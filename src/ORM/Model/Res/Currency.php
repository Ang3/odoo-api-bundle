<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\ActivatableRecordTrait;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Model("res.currency")
 *
 * @author Joanis ROUANET
 */
class Currency extends Record
{
    use ActivatableRecordTrait;
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