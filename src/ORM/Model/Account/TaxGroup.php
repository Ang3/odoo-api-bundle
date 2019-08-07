<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Account;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Model("account.tax.group")
 *
 * @author Joanis ROUANET
 */
class TaxGroup extends Record
{
    use NamedRecordTrait;

    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("sequence")
     */
    protected $sequence;

    /**
     * @param int|null $sequence
     *
     * @return self
     */
    public function setSequence(int $sequence = null)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSequence()
    {
        return $this->sequence;
    }
}
