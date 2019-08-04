<?php

namespace Ang3\Bundle\OdooApiBundle\Model\Res;

use Ang3\Bundle\OdooApiBundle\Model\Record;
use Ang3\Bundle\OdooApiBundle\Model\ActivatableRecordTrait;

/**
 * @author Joanis ROUANET
 */
class User extends Record
{
    use ActivatableRecordTrait;
    use ContactTypeTrait;
}
