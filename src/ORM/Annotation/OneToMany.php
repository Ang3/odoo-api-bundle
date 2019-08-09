<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Annotation;

/**
 * @author Joanis ROUANET
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("class", type = "string")
 * })
 */
class OneToMany
{
    /**
     * @Required
     *
     * @var string
     */
    public $class;
}
