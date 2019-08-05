<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

/**
 * @author Joanis ROUANET
 */
class Field
{
    /**
     * Field type constants.
     */
    const SIMPLE = 'simple';
    const MANY_TO_ONE = 'manyToOne';
    const ONE_TO_MANY = 'oneToMany';
    const MANY_TO_MANY = 'manyToMany';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @param string      $name
     * @param string|null $type
     */
    public function __construct(string $name, string $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }
}
