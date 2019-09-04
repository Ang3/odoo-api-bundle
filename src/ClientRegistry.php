<?php

namespace Ang3\Bundle\OdooApiBundle;

use LogicException;
use Ang3\Component\Odoo\Client\ExternalApiClient;

/**
 * @author Joanis ROUANET
 */
class ClientRegistry
{
    /**
     * @var array
     */
    private $registry = [];

    /**
     * @param string            $name
     * @param ExternalApiClient $client
     *
     * @return self
     */
    public function set(string $name, ExternalApiClient $client)
    {
        $this->registry[$name] = $client;

        return $this;
    }

    /**
     * @param string $name
     *
     * @throws LogicException when the connection does not exist
     *
     * @return ExternalApiClient
     */
    public function get(string $name)
    {
        // Si on a pas le client
        if (!$this->has($name)) {
            throw new LogicException(sprintf('The Odoo connection "%s" does not exists', $name));
        }

        // Retour du client
        return $this->registry[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->registry);
    }
}
