<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Exception;

use RuntimeException;
use Throwable;

/**
 * @author Joanis ROUANET
 */
class DenormalizationException extends RuntimeException
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $class;

    /**
     * @param array          $data
     * @param string         $class
     * @param string         $message
     * @param Throwable|null $previous
     */
    public function __construct(array $data, string $class, string $message, Throwable $previous = null)
    {
        // Construction de l'exception de base
        parent::__construct($message, 0, $previous);

        // Hydratation
        $this->data = $data;
        $this->class = $class;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
