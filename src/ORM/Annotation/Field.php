<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Annotation;

/**
 * @author Joanis ROUANET
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("type", type = "string")
 * })
 */
class Field
{
    /**
     * @var string|null
     */
    public $type;
}
