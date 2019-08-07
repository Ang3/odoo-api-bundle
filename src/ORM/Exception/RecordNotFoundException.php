<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Exception;

use RuntimeException;
use Ang3\Component\OdooApiClient\ExternalApiClient;

/**
 * @author Joanis ROUANET
 */
class RecordNotFoundException extends RuntimeException
{
    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @var string
     */
    private $class;

    /**
     * @var int
     */
    private $id;

    /**
     * Constructor of the exception.
     *
     * @param ExternalApiClient $client
     * @param string            $class
     * @param int               $id
     */
    public function __construct(ExternalApiClient $client, string $class, int $id)
    {
        // Hydratation
        $this->client = $client;
        $this->class = $class;
        $this->id = $id;

        // Construction de l'exception parente
        parent::__construct(sprintf('Record %d of class "%s" not found', $id, $class));
    }

    /**
     * @return ExternalApiClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
