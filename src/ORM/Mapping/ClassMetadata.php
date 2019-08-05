<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use InvalidArgumentException;
use ReflectionClass;

/**
 * @author Joanis ROUANET
 */
class ClassMetadata
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $model;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @param string $class
     * @param string $model
     * @param array  $fields
     */
    public function __construct(string $class, string $model, array $fields = [])
    {
        // Hydratation
        $this->class = $class;
        $this->model = $model;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param  string $name
     * 
     * @return array
     */
    public function getField($name)
    {
        // Si le champ n'existe pas
        if(!$this->hasField($name)) {
            throw new InvalidArgumentException(sprintf('The field "%s" does not exist in class "%s"', $name, $this->class));
        }

        // Retour du champ
        return $this->fields[$name];
    }

    /**
     * Get reflection a the class.
     * 
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        return new ReflectionClass($this->class);
    }

    /**
     * @return bool
     */
    public function isSimpleField(string $name)
    {
        return $this->hasField($name) && Field::SIMPLE === $this->fields[$name]->getType();
    }

    /**
     * @return bool
     */
    public function isSingledValueAssociation(string $name)
    {
        return $this->hasField($name) && Field::MANY_TO_ONE === $this->fields[$name]['type'];
    }

    /**
     * @return bool
     */
    public function isMultipledValueAssociation(string $name)
    {
        return $this->hasField($name) && in_array($this->fields[$name]['type'], [Field::ONE_TO_MANY, Field::MANY_TO_MANY]);
    }

    /**
     * @return bool
     */
    public function hasField(string $name)
    {
        return array_key_exists($name, $this->fields);
    }
}
