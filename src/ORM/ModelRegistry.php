<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Ang3\Bundle\OdooApiBundle\Annotations;

/**
 * @author Joanis ROUANET
 */
class ModelRegistry
{
	/**
	 * @var array
	 */
	private $classMap;

	/**
	 * Register a model class.
	 * 
	 * @param string $class
	 * @param string $name
	 *
	 * @return self
	 */
	public function register($class, $name)
	{
		// Si la classe n'existe pas
		if(!class_exists($class)) {
			throw new Exception(sprintf('The Odoo model class "%s" does not exist', $class));
		}

		// Enregistrement
		$this->classMap[$class] = $name;

		// Retour du registre
		return $this;
	}

	/**
	 * Resolve Odoo model name from class.
	 * 
	 * @param  string $class
	 *
	 * @throws Exception When the class does not represent an Odoo model.
	 * 
	 * @return string
	 */
	public function resolve($class)
	{
		if(!array_key_exists($class, $this->classMap)) {
			throw new Exception(sprintf('The class "%s" does not represent an Odoo model - Did you forget to implement annotation "%s" on class?', $class, Annotations\Model::class));
		}

		return $this->classMap[$class];
	}

	/**
	 * Get the class of a model.
	 * 
	 * @param  string $model
	 *
	 * @throws Exception When no class found for the model.
	 * 
	 * @return string
	 */
	public function getClass($model)
	{
		// Recherche du modèle par la classe
		$class = array_search($model, $this->classMap);

		// Retour de la clé sinon NULL
		return false !== $model ? (string) $model : null;

		// Si pas de clé
		if(false === $class) {
			throw new Exception(sprintf('No class found for the model "%s"', $model));
		}

		// Retour de la classe du modèle
		return $class;
	}

	/**
	 * Register a model class.
	 * 
	 * @param string $name
	 * @param string $class
	 *
	 * @return self
	 */
	public function delete($name)
	{
		// Si le modèle existe
		if(!empty($this->models[$name])) {
			// Suppression du modèle
			unset($this->models[$name]);
		}

		// Retour du registre
		return $this;
	}
}