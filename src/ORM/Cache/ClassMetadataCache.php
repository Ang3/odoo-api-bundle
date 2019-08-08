<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Cache;

use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @author Joanis ROUANET
 */
class ClassMetadataCache
{
    /**
     * @var ArrayAdapter
     */
    private $cache;

    /**
     * Constructor of class metadata cache.
     */
    public function __construct()
    {
        $this->cache = new ArrayAdapter(0, false);
    }

    /**
     * Set cached class metadata.
     *
     * @param string        $class
     * @param ClassMetadata $classMetadata
     *
     * @return self
     */
    public function set(string $class, ClassMetadata $classMetadata)
    {
        // Récupération de l'entrée du cache
        $item = $this->cache->getItem($this->generateCacheKey($class));

        // Enregistrement des métadonnées de la classe
        $item->set($classMetadata);

        // Sauvegarde de l'élément en cache
        $this->cache->save($item);
    }

    /**
     * Get cached class metadata.
     *
     * @param string $class
     *
     * @return ClassMetadata|null
     */
    public function get(string $class)
    {
        // Récupération de l'entrée du cache
        $item = $this->cache->getItem($this->generateCacheKey($class));

        // Si on a l'élément en cache
        if ($item->isHit()) {
            /**
             * Récupération de l'élément en cache.
             *
             * @var ClassMetadata
             */
            $classMetadata = $item->get();

            // Retour des métadonnées
            return $classMetadata;
        }

        // Retour nul par défaut
        return null;
    }

    /**
     * Remove class metadata from the cache.
     *
     * @param string $class
     *
     * @return self
     */
    public function remove(string $class)
    {
        $this->cache->deleteItem($class);

        return $this;
    }

    /**
     * Generate a key for cache item.
     *
     * @internal
     *
     * @param string $class
     *
     * @return string
     */
    private function generateCacheKey(string $class)
    {
        return sprintf('ang3_odoo_api.metadata.%s', str_replace('\\', '_', $class));
    }
}
