<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use ReflectionClass;
use InvalidArgumentException;
use Ang3\Bundle\OdooApiBundle\Annotations;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;

/**
 * @author Joanis ROUANET
 */
class ModelRegistry
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $classMap = [];

    /**
     * @param Reader $reader
     * @param array  $parameters
     */
    public function __construct(Reader $reader, array $parameters)
    {
        // Hydratation
        $this->reader = $reader;
        $this->parameters = $parameters;

        // Configuration des maps de classe
        $this->configure($parameters);
    }

    /**
     * Configure class map from parameters.
     *
     * @param array $parameters
     */
    public function configure(array $parameters)
    {
        // Pour chaque classe dans la configuration
        foreach ($parameters as $class) {
            // Si la classe n'implémente pas l'interface d'enregistrement de Odoo
            if (!in_array(RecordInterface::class, class_implements($class))) {
                throw new InvalidArgumentException(sprintf('The class "%s" does not represent a record. Did you forget to implement interface "%s"?', $class, RecordInterface::class));
            }

            // Si la classe du modèle n'existe pas
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Model class "%s" not found.', $class));
            }

            // Réflection du modèle
            $reflection = new ReflectionClass($class);

            // Recherche de l'annotation "Model"
            $annotation = $this->reader->getClassAnnotation($reflection, Annotations\Model::class);

            // Si pas d'annotation de modèle
            if (!($annotation instanceof Annotations\Model)) {
                throw new InvalidArgumentException(sprintf('The class "%s" does not represent a model. Did you forget to implement annotation "%s"?', $class, Annotations\Model::class));
            }

            // Enregistrement de la classe et de son modèle associé
            $this->register($class, $annotation->name);
        }
    }

    /**
     * Register a model class.
     *
     * @param string $class
     * @param string $name
     *
     * @throws InvalidArgumentException when the class does not exist
     *
     * @return self
     */
    public function register($class, $name)
    {
        // Si la classe n'existe pas
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('The Odoo model class "%s" does not exist', $class));
        }

        // Enregistrement
        $this->classMap[$class] = $name;

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
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : (string) $objectOrClass;

        // Si la classe n'est pas un modèle Odoo
        if (!array_key_exists($class, $this->classMap)) {
            throw new InvalidArgumentException(sprintf('The class "%s" does not represent an Odoo model - Did you forget to implement annotation "%s" on class?', $class, Annotations\Model::class));
        }

        // Retour du nom du modèle
        return $this->classMap[$class];
    }

    /**
     * Get the class of a model.
     *
     * @param string $model
     *
     * @throws InvalidArgumentException when no class found for the model
     *
     * @return string
     */
    public function getClass($model)
    {
        // Recherche du modèle par la classe
        $class = array_search($model, $this->classMap);

        // Si pas de clé
        if (false === $class) {
            throw new InvalidArgumentException(sprintf('No class found for the model "%s"', $model));
        }

        // Retour de la classe du modèle
        return $class;
    }
}
