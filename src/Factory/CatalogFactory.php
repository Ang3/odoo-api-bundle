<?php

namespace Ang3\Bundle\OdooApiBundle\Factory;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Catalog;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Finder\Finder;

/**
 * @author Joanis ROUANET
 */
class CatalogFactory
{
	/**
	 * @var CacheInterface
	 */
	private $systemCache;

    /**
     * @var Reader
     */
    private $reader;

	/**
	 * @param Finder $finder
	 * @param Reader $reader
	 * @param string $projectDir
	 */
	public function __construct(CacheInterface $systemCache, Reader $reader)
	{
		$this->systemCache = $systemCache;
		$this->reader = $reader;
	}

	/**
	 * Create a new catalog from prefix and directories.
	 * 
	 * @param  array $mapping
	 * @param  bool  $loadDefaults
	 * 
	 * @return Catalog
	 */
	public function create(array $mapping, bool $loadDefaults = true)
	{
		if(true === $loadDefaults) {
			$mapping = array_merge([
				'Ang3\Bundle\OdooApiBundle' => sprintf('%s/..', __DIR__)
			], $mapping);
		}

		dump($mapping);

		// Création du catalogue
		$catalog = new Catalog;

		// Récupération du lecteur d'annotations
		$reader = $this->reader;

		// Pour chaque entrée du mapping
		foreach($mapping as $prefix => $directories) {
			// Formatage des répertoire dans un tableau
			$directories = (array) $directories;

			// Pour chaque répertoire
			foreach($directories as $directory) {
				// Génération de la clé en cache
				$cacheKey = $this->generateCacheKey($prefix, $directory);

				// Récupération de l'item depuis le cache
				$cacheItem = $this->systemCache->getItem($cacheKey);

				// Si pas d'entrée dans le cache
				if(!$cacheItem->isHit()) {
					// Recherche des modèles
					$models = $this->findModelClasses($prefix, $directory);

					// Assignation de la valeur sérialisé dans l'item du cache'
					$cacheItem->set(serialize($models));

					// Enregistrement de l'item
					$this->systemCache->save($cacheItem);
				}

				// Récupération des modèles après désérialisation depuis le cache
				$models = unserialize($cacheItem->get());

				// Pour chaque modèle
				foreach($models as $name => $class) {
					// Enregistrement du modèle dans le catalogue
					$catalog->register($name, $class);
				}
			}
		}

		// Retour du catalogue
		return $catalog;
	}

	/**
	 * Find model classes from prefix and directory path.
	 * 
	 * @param  string $prefix
	 * @param  string $directory
	 * 
	 * @return array
	 */
	public function findModelClasses(string $prefix, string $directory)
	{
		// Initialisation des modèles
		$models = [];

		// Création d'un rechercheur de fichiers
		$finder = new Finder;

		// Recherche des fichiers PHP depuis le répertoire
		$finder->files()->in($directory)->name('*.php');

		// Pour chaque fichier
		foreach ($finder as $file) {
			// Définition du FQCN de la classe
		    $class = str_replace(DIRECTORY_SEPARATOR, '\\', substr(sprintf('%s/%s', $prefix, $file->getRelativePathname()), 0, -4));

		    // Si on a une classe
		    if(class_exists($class)) {
		    	// Réflection de la classe
		    	$reflection = new ReflectionClass($class);

		    	/**
		    	 * Tentative de récupération de l'annotation du modèle
		    	 * 
		    	 * @var ORM\Model|null
		    	 */
		    	$model = $this->reader->getClassAnnotation($reflection, ORM\Model::class);

		    	// Si on a une annotation de modèle
		    	if(null !== $model) {
		    		// Si la classe n'implémente pas l'interface d'enregistrement Odoo
			    	if(!$reflection->implementsInterface(RecordInterface::class)) {
			    		throw new MappingException(sprintf('The Odoo model class "%s" (%s) must implements interface "%s"', $class, $model->name, RecordInterface::class));
			    	}

		    		// Enregistrement de la classe du modèle selon son nom annoté
		    		$models[$model->name] = $class;
		    	}
		    }
		}

		// Retour des modèles
		return $models;
	}

	/**
	 * Generate a key for cache item.
	 * 
	 * @param  string $prefix
	 * @param  string $directory
	 * 
	 * @return string
	 */
	private function generateCacheKey(string $prefix, string $directory)
	{
		return sprintf('ang3_odoo_api.models.%s.%s', str_replace('\\', '_', $prefix), str_replace(DIRECTORY_SEPARATOR, '_', $directory));
	}
}