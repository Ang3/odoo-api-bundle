<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Annotation;

/**
 * @author Joanis ROUANET
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("name", type = "name"),
 *   @Attribute("class", type = "string"),
 *   @Attribute("nullable", type = "boolean"),
 *   @Attribute("options", type = "array")
 * })
 */
class ManyToMany
{
    /**
     * @var string
     */
    public $name;

    /**
     * @Required
     *
     * @var string
     */
    public $class;

    /**
     * @var array
     */
    public $options = [];
}
