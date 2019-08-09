<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\ActivatableRecordTrait;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;

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
     * @ORM\Field(name="symbol", type="string, nullable=false)
     */
    private $symbol;

    /**
     * @var float
     *
     * @ORM\Field(name="rate", type="float", nullable=false)
     */
    private $rate;

    /**
     * @var float|null
     *
     * @ORM\Field(name="rounding", type="float")
     */
    private $rounding;

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

    /**
     * @param float|null $rounding
     *
     * @return self
     */
    public function setRounding(float $rounding = null)
    {
        $this->rounding = $rounding;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getRounding()
    {
        return $this->rounding;
    }
}
