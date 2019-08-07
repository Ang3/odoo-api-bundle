<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Annotation;

/**
 * @author Joanis ROUANET
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string")
 * })
 */
class Model
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;
}
