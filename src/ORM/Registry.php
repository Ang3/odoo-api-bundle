<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Exception;

/**
 * @author Joanis ROUANET
 */
class Registry
{
    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var RecordManager[]
     */
    private $managers = [];

    /**
     * @param string $defaultConnection
     */
    public function __construct(string $defaultConnection)
    {
        $this->defaultConnection = $defaultConnection;
    }

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
     * @param string|null $name
     *
     * @throws Exception when the manager was not found
     *
     * @return RecordManager
     */
    public function get(string $name = null)
    {
        // Si pas de nom
        if (null === $name) {
            // Retour du manager de la connection par défaut
            return $this->get($this->defaultConnection);
        }

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
