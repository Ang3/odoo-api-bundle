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
 *   @Attribute("nullable", type = "boolean"),
 *   @Attribute("options", type = "array")
 * })
 */
class Field
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var bool
     */
    public $nullable = true;

    /**
     * @var array
     */
    public $options = [];
}
