<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Annotation;

/**
 * @author Joanis ROUANET
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("name", type = "name"),
 *   @Attribute("type", type = "string"),
 *   @Attribute("options", type = "array")
 * })
 */
class Field
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var array
     */
    public $options = [];
}
