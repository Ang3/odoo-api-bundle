<?php

namespace Ang3\Bundle\OdooApiBundle\Annotations;

/**
 * @author Joanis ROUANET
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("class", type = "string"),
 *   @Attribute("nullable", type = "boolean")
 * })
 */
class ManyToOne
{
    /**
     * @Required
     *
     * @var string
     */
    public $class;

    /**
     * @var bool
     */
    public $nullable = true;
}
