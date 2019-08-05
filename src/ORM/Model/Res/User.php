<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\ActivatableRecordTrait;

/**
 * @author Joanis ROUANET
 */
class User extends Record
{
    use ActivatableRecordTrait;
    use ContactTypeTrait;
}
