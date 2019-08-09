<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Exception;

use Throwable;

/**
 * @author Joanis ROUANET
 */
class MappingTypeException extends MappingException
{
    /**
     * Invalid type of a value.
     *
     * @param mixed          $value
     * @param string         $toType
     * @param array          $expectedTypes
     * @param Throwable|null $previous
     *
     * @return self
     */
    public static function conversionFailedInvalidType($value, string $toType, array $expectedTypes, Throwable $previous = null)
    {
        return new static(sprintf('Unable to convert value to type "%s" - Excepted value of type "%s", "%s" given', $toType, implode('|', $expectedTypes), is_object($value) ? get_class($value) : gettype($value)), 0, $previous);
    }
}
