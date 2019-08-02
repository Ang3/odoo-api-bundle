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
}
