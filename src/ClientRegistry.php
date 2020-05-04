<?php

namespace Ang3\Bundle\OdooApiBundle;

use Ang3\Component\Odoo\Client;
use LogicException;

/**
 * @author Joanis ROUANET
 */
class ClientRegistry
{
    /**
     * @var array
     */
    private $registry = [];

    public function set(string $name, Client $client): self
    {
        $this->registry[$name] = $client;

        return $this;
    }

    /**
     * @throws LogicException when the connection does not exist
     */
    public function get(string $name): Client
    {
        // Si on a pas le client
        if (!$this->has($name)) {
            throw new LogicException(sprintf('The Odoo connection "%s" does not exist', $name));
        }

        // Retour du client
        return $this->registry[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->registry);
    }
}
