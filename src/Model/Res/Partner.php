<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Model\AbstractRecord;
use Ang3\Bundle\OdooApiBundle\Model\ActivatableModelTrait;

/**
 * @author Joanis ROUANET
 */
class Partner extends AbstractRecord
{
    use ActivatableModelTrait;
    use ContactTypeTrait;

    /**
     * Constructor of the record.
     *
     * @param string $displayName
     * @param string $name
     */
    public function __construct($displayName, $name = null)
    {
        // Hydratation
        $this->displayName = $displayName;
        $this->name = $name ?: $displayName;
    }
}
