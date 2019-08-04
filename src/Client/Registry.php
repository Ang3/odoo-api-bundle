<?php

namespace Ang3\Bundle\OdooApiBundle\Client;

use Exception;
use Ang3\Component\OdooApiClient\ExternalApiClient;

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
	 * @var ExternalApiClient[]
	 */
	private $clients = [];

	/**
	 * Register a client by name.
	 * 
	 * @param  string            $name
	 * @param  ExternalApiClient $client
	 * 
	 * @return self
	 */
	public function register(string $name, ExternalApiClient $client)
	{
		$this->clients[$name] = $client;

		return $this;
	}

	/**
	 * Get a client by name.
	 * 
	 * @param  string $name
	 *
	 * @throws Exception When the client was not found.
	 * 
	 * @return ExternalApiClient
	 */
	public function get(string $name = self::DEFAULT_NAME)
	{
		// Si pas de client
		if(!$this->has($name)) {
			throw new Exception(sprintf('Odoo external API client "%s" not found.', $name));
		}

		// Retour du client
		return $this->clients[$name];
	}

	/**
	 * Check if a client exists.
	 * 
	 * @param  string $name
	 * 
	 * @return bool
	 */
	public function has(string $name)
	{
		return array_key_exists($name, $this->clients);
	}
}