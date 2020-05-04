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

    public function set(string $connectionName, Client $client): self
    {
        $this->registry[$connectionName] = $client;

        return $this;
    }

    /**
     * @throws LogicException when the connection does not exist
     */
    public function get(string $connectionName): Client
    {
        // Si on a pas le client
        if (!$this->has($connectionName)) {
            throw new LogicException(sprintf('The Odoo connection "%s" does not exist', $connectionName));
        }

        // Retour du client
        return $this->registry[$connectionName];
    }

    public function has(string $connectionName): bool
    {
        return array_key_exists($connectionName, $this->registry);
    }
}
