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
     * @var ReflectionClass
     */
    private $reflection;

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
     */
    public function __construct(string $class)
    {
        $this->reflection = new ReflectionClass($class);
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
     * @param FieldInterface $field
     *
     * @return self
     */
    public function addField(FieldInterface $field)
    {
        // Enregistrement du champ
        $this->fields[$field->getName()] = $field;

        // Retour des métadonnées
        return $this;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function removeField(string $name)
    {
        // Si le champ est déjà enregistré
        if ($this->hasField($name)) {
            // Suppression du champ
            unset($this->fields[$name]);
        }

        // Retour des métadonnées
        return $this;
    }

    /**
     * @param string $name
     *
     * @throws MappingException when the field was not found
     *
     * @return FieldInterface
     */
    public function getField($name)
    {
        // Si pas cette propriété
        if (!$this->hasField($name)) {
            throw new MappingException(sprintf('The field "%s" does not exists in metadata of class "%s"', $name, $this->reflection->getName()));
        }

        // Retour de la propriété
        return $this->fields[$name];
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
        // Si pas cette propriété
        if (!$this->hasField($name)) {
            throw new MappingException(sprintf('The field "%s" does not exists in metadata of class "%s"', $name, $this->reflection->getName()));
        }

        // Récupération du champ
        $field = $this->fields[$name];

        // Si pas cette propriété
        if (!$field->isAssociation()) {
            throw new MappingException(sprintf('The field "%s" is not an association in class "%s"', $name, $this->reflection->getName()));
        }

        // Retour de la propriété
        return $field;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasField($name)
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasSimpleField(string $name)
    {
        return $this->hasField($name) && $this->fields[$name]->isField();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAssociation(string $name)
    {
        return $this->hasField($name) && $this->fields[$name]->isAssociation();
    }

    /**
     * Get all serialized fields names.
     *
     * @return array
     */
    public function getSerializedNames()
    {
        // Initialisation de la liste des noms sérialisés
        $serializedNames = [];

        // Pour chaque propriété
        foreach ($this->iterateFields() as $name => $field) {
            // Enregistrement du nom sérialisé
            $serializedNames[$field->getName()] = $field->getSerializedName();
        }

        // Retour des noms sérialisés
        return $serializedNames;
    }

    /**
     * @return Generator
     */
    public function iterateFields()
    {
        // Pour chaque propriété
        foreach ($this->fields as $name => $field) {
            // On rend le champ avec son nom en clé
            yield $name => $field;
        }
    }

    /**
     * @return Generator
     */
    public function iterateSimpleFields()
    {
        // Pour chaque propriété
        foreach ($this->fields as $name => $field) {
            // Si c'est un champ simple
            if ($field->isField()) {
                // On rend le champ avec son nom en clé
                yield $name => $field;
            }
        }
    }

    /**
     * @return Generator
     */
    public function iterateAssociations()
    {
        // Pour chaque propriété
        foreach ($this->fields as $name => $field) {
            // Si c'est une association
            if ($field->isAssociation()) {
                // On rend le champ avec son nom en clé
                yield $name => $field;
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
        return $this->reflection;
    }

    /**
     * Get the FQCN of mapped class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->reflection->getName();
    }
}
