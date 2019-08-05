<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Exception;

/**
 * @author Joanis ROUANET
 */
class Registry
{
    /**
     * Default registry name.
     */
    const DEFAULT_NAME = 'default';

    /**
     * @var RecordManager[]
     */
    private $managers = [];

    /**
     * Register a manager by name.
     *
     * @param string        $name
     * @param RecordManager $manager
     *
     * @return self
     */
    public function register(string $name, RecordManager $manager)
    {
        $this->managers[$name] = $manager;

        return $this;
    }

    /**
     * Get a manager by name.
     *
     * @param string $name
     *
     * @throws Exception when the manager was not found
     *
     * @return RecordManager
     */
    public function get(string $name = self::DEFAULT_NAME)
    {
        // Si pas de manager
        if (!$this->has($name)) {
            throw new Exception(sprintf('Odoo record manager "%s" not found.', $name));
        }

        // Retour du manager
        return $this->managers[$name];
    }

    /**
     * Check if a manager exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->managers);
    }
}
