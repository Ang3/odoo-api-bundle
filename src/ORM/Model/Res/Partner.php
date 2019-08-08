<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Res;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\ActivatableRecordTrait;

/**
 * @ORM\Model("res.partner")
 *
 * @author Joanis ROUANET
 */
class Partner extends Record
{
    use ActivatableRecordTrait;
    use ContactTypeTrait;
}
