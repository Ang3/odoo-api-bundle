<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Exception;

use RuntimeException;
use Throwable;

/**
 * @author Joanis ROUANET
 */
class NormalizationException extends RuntimeException
{
    /**
     * @var object
     */
    private $object;

    /**
     * @param object         $object
     * @param string         $message
     * @param Throwable|null $previous
     */
    public function __construct(object $object, string $message, Throwable $previous = null)
    {
        // Construction de l'exception de base
        parent::__construct($message, 0, $previous);

        // Hydratation
        $this->object = $object;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }
}
