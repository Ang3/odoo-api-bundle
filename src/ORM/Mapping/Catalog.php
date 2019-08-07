<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * A catalog is a list of record classes grouped by model name.
 *
 * @author Joanis ROUANET
 */
class Catalog
{
    /**
     * @var array
     */
    private $models = [];

    /**
     * Register a model class.
     *
     * @param string $name
     * @param string $class
     *
     * @throws MappingException when the class is not valid
     *
     * @return self
     */
    public function register(string $name, string $class)
    {
        // Si la classe n'existe pas
        if (!class_exists($class)) {
            throw new MappingException(sprintf('The Odoo model class "%s" does not exist', $class));
        }

        // Réflection de la classe
        $reflection = new ReflectionClass($class);

        // Si la classe n'implémente pas l'interface d'enregistrement Odoo
        if (!$reflection->implementsInterface(RecordInterface::class)) {
            throw new MappingException(sprintf('The class "%s" does not implements record interface "%s"', $class, RecordInterface::class));
        }

        // Si la classe n'est pas déjà enregistrée
        if (!array_key_exists($name, $this->models)) {
            // Initialisation des classes
            $this->models[$name] = [];
        }

        // Si la classe n'est pas déjà enregistrée
        if (!in_array($class, $this->models[$name])) {
            // Enregistrement
            $this->models[$name][] = $class;
        }

        // Retour du registre
        return $this;
    }

    /**
     * Resolve Odoo model name from object or class.
     *
     * @param object|scalar $objectOrClass
     *
     * @throws MappingException when the class does not represent an Odoo model
     *
     * @return string
     */
    public function getModel($objectOrClass)
    {
        // Récuépration du nom complet de la classe
        $class = is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : (string) $objectOrClass;

        // Pour chaque modèle
        foreach ($this->models as $name => $classes) {
            // Si la classe est contenu dans la liste
            if (in_array($class, $classes)) {
                // Retour du nom du modèle
                return $name;
            }
        }

        throw new MappingException(sprintf('The class "%s" does not represent an Odoo model - Did you forget to add the associated model in configuration?', $class));
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
        return array_key_exists($name, $this->models);
    }

    /**
     * Get the class of a name.
     *
     * @param string $name
     *
     * @throws MappingException when no class found for the model
     *
     * @return array
     */
    public function getClasses($name)
    {
        // Si pas de clé
        if (!$this->hasModel($name)) {
            throw new MappingException(sprintf('No class found for the model "%s"', $name));
        }

        // Retour de la classe du modèle
        return $this->models[$name];
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
        // Pour chaque modèle
        foreach ($this->models as $name => $classes) {
            // Si la classe est contenu dans la liste
            if (in_array($class, $classes)) {
                // Retour positif
                return true;
            }
        }

        // Retour négatif par défaut
        return false;
    }
}
