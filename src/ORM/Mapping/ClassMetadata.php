<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Generator;
use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;

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
     * @var ReflectionClass|null
     */
    private $reflectionClass;

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @var array
     */
    private $maps = [];

    /**
     * @param string $class
     * @param string $model
     */
    public function __construct(string $class, string $model)
    {
        $this->class = $class;
        $this->model = $model;
    }

    /**
     * @param string $class
     *
     * @return self
     */
    public function setClass(string $class)
    {
        $this->class = $class;

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
     * @param string $model
     *
     * @return self
     */
    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param PropertyInterface $property
     *
     * @return self
     */
    public function addProperty(PropertyInterface $property)
    {
        // Si la propriété est déjà mappée
        if($this->hasProperty($property->getLocalName())) {
            throw new MappingException(sprintf('The property "%s::$%s" is already mapped.', $this->class, $property->getLocalName()));
        }

        // Si la propriété est déjà mappée
        if($this->isMapped($property->getRemoteName())) {
            throw new MappingException(sprintf('The remote name "%s" for the class "%s" is already mapped to another property.', $property->getRemoteName(), $this->class));
        }

        // Enregistrement du champ
        $this->properties[$property->getLocalName()] = $property;
        $this->maps[$property->getRemoteName()] = $property->getLocalName();

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
            unset($this->maps[$this->properties[$name]->getLocalName()]);
            unset($this->properties[$name]);
        }

        // Retour des métadonnées
        return $this;
    }

    /**
     * @param string $name
     *
     * @throws MappingException when the field was not found
     *
     * @return FieldMetadata
     */
    public function getField($name)
    {
        // Récupération de la propriété
        $property = $this->getProperty($name);

        // Si la propriété ne représente pas un champ simple
        if (!$property->isField()) {
            throw new MappingException(sprintf('The property "%s" is not a field in class "%s"', $name, $this->class));
        }

        /**
         * @var FieldMetadata
         */
        $property = $property;

        // Retour de la propriété
        return $property;
    }

    /**
     * @param string $name
     *
     * @throws MappingException when the association was not found
     *
     * @return AssociationMetadata
     */
    public function getAssociation($name)
    {
        // Récupération de la propriété
        $property = $this->getProperty($name);

        // Si pas cette propriété
        if (!$property->isAssociation()) {
            throw new MappingException(sprintf('The property "%s" is not an association in class "%s"', $name, $this->class));
        }

        /**
         * @var AssociationMetadata
         */
        $property = $property;

        // Retour de la propriété
        return $property;
    }

    /**
     * @param string $name
     *
     * @throws MappingException when the property was not found
     *
     * @return PropertyInterface
     */
    public function getProperty($name)
    {
        // Si pas cette propriété
        if (!$this->hasProperty($name)) {
            throw new MappingException(sprintf('The property "%s" is not mapped in metadata of class "%s"', $name, $this->class));
        }

        // Retour de la propriété
        return $this->properties[$name];
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Resolve property from mapped name.
     * 
     * @param  string $name
     * 
     * @return PropertyInterface|null
     */
    public function resolveMapped($name)
    {
        // Si la propriété n'est pas mappée
        if(!$this->isMapped($name)) {
            // Retour null
            return null;
        }

        // Retour de la propriété mappée
        return $this->properties[$this->maps[$name]];
    }

    /**
     * Check if a name is mapped.
     * 
     * @param  string $name
     * 
     * @return PropertyInterface|null
     */
    public function isMapped($name)
    {
        return array_key_exists($name, $this->maps);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name)
    {
        return $this->hasProperty($name) && $this->properties[$name]->isField();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAssociation(string $name)
    {
        return $this->hasProperty($name) && $this->properties[$name]->isAssociation();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @return Generator
     */
    public function iterateProperties()
    {
        // Pour chaque propriété
        foreach ($this->properties as $name => $property) {
            // On rend le champ avec son nom en clé
            yield $name => $property;
        }
    }

    /**
     * @return Generator
     */
    public function iterateFields()
    {
        // Pour chaque propriété
        foreach ($this->properties as $name => $property) {
            // Si c'est un champ simple
            if ($property->isField()) {
                // On rend le champ avec son nom en clé
                yield $name => $property;
            }
        }
    }

    /**
     * @return Generator
     */
    public function iterateAssociations()
    {
        // Pour chaque propriété
        foreach ($this->properties as $name => $property) {
            // Si c'est une association
            if ($property->isAssociation()) {
                // On rend le champ avec son nom en clé
                yield $name => $property;
            }
        }
    }

    /**
     * Get reflection class.
     *
     * @return ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflectionClass = $this->reflectionClass ?: new ReflectionClass($this->class);
    }
}
