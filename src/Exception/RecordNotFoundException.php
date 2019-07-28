<?php

namespace Ang3\Bundle\OdooApiBundle\Exception;

use RuntimeException;
use Ang3\Component\OdooApiClient\Client\ExternalApiClient;

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
    private $model;

    /**
     * @var int
     */
    private $id;

    /**
     * Constructor of the exception.
     *
     * @param ExternalApiClient $client
     * @param string            $model
     * @param int               $id
     */
    public function __construct(ExternalApiClient $client, string $model, int $id)
    {
        // Hydratation
        $this->client = $client;
        $this->model = $model;
        $this->id = $id;

        // Construction de l'exception parente
        parent::__construct(sprintf('Record %d of model "%s" not found', $id, $model));
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
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
