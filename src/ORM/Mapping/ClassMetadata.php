<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Generator;
use ReflectionClass;
use ReflectionProperty;

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
     * @var ReflectionClass
     */
    private $reflection;

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->setClass($class);
    }

    /**
     * @param string $class
     *
     * @return self
     */
    public function setClass(string $class)
    {
        $this->class = $class;
        $this->reflection = new ReflectionClass($class);

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflection;
    }

    /**
     * @param PropertyInterface $property
     *
     * @return self
     */
    public function addProperty(PropertyInterface $property)
    {
        // Enregistrement du champ
        $this->properties[$property->getName()] = $property;

        // Retour des métadonnées
        return $this;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function removeProperty(string $name)
    {
        // Si le champ est déjà enregistré
        if ($this->hasProperty($name)) {
            // Suppression du champ
            unset($this->properties[$name]);
        }

        // Retour des métadonnées
        return $this;
    }

    /**
     * @return array
     */
    public function getPropertys()
    {
        return $this->properties;
    }

    /**
     * @return Generator
     */
    public function iteratePropertys()
    {
        // Pour chaque champ
        foreach ($this->properties as $name => $property) {
            // On rend le champ avec son nom en clé
            yield $name => $property;
        }
    }

    /**
     * Iterate on reflection properties.
     *
     * @return Generator
     */
    public function iterateProperties()
    {
        // Pour chaque champ
        foreach ($this->getProperties() as $name => $property) {
            // On rend le champ avec son nom en clé
            yield $name => $property;
        }
    }

    /**
     * Get reflection properties.
     *
     * @return ReflectionProperty[]
     */
    public function getProperties()
    {
        return $this->reflection->getProperties();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty(string $name)
    {
        return array_key_exists($name, $this->properties);
    }
}
