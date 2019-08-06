<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use InvalidArgumentException;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Joanis ROUANET
 */
class ModelRegistry
{
    /**
     * @var array
     */
    private $mapping = [];

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        // Hydratation
        $this->mapping = $mapping;
    }

    /**
     * Register a model class.
     *
     * @param string $name
     * @param string $class
     *
     * @throws InvalidArgumentException when the class does not exist
     *
     * @return self
     */
    public function register(string $name, string $class)
    {
        // Si la classe n'existe pas
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('The Odoo model class "%s" does not exist', $class));
        }

        // Enregistrement
        $this->mapping[$name] = $class;

        // Retour du registre
        return $this;
    }

    /**
     * Resolve Odoo model name from object or class.
     *
     * @param object|scalar $objectOrClass
     *
     * @throws InvalidArgumentException when the class does not represent an Odoo model
     *
     * @return string
     */
    public function resolve($objectOrClass)
    {
        // Récuépration du nom complet de la classe
        $class = is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : (string) $objectOrClass;

        // Recherche du modèle par la classe
        $name = array_search($class, $this->mapping);

        // Si la classe n'est pas un modèle Odoo
        if (false === $name) {
            throw new InvalidArgumentException(sprintf('The class "%s" does not represent an Odoo model - Did you forget to add the associated model in configuration?', $class));
        }

        // Retour du nom du modèle
        return $name;
    }

    /**
     * Get the class of a name.
     *
     * @param string $name
     *
     * @throws InvalidArgumentException when no class found for the model
     *
     * @return string
     */
    public function getClass($name)
    {
        // Si pas de clé
        if (!$this->hasModel($name)) {
            throw new InvalidArgumentException(sprintf('No class found for the model "%s"', $name));
        }

        // Retour de la classe du modèle
        return $this->mapping[$name];
    }

    /**
     * Check if a model exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasModel(string $name)
    {
        return array_key_exists($name, $this->mapping);
    }

    /**
     * Check if the class is registered for a model.
     *
     * @param string $class
     *
     * @return bool
     */
    public function hasClass(string $class)
    {
        return in_array($class, $this->mapping);
    }
}
