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
	 * @param  mixed          $value
	 * @param  array          $expectedTypes
	 * @param  Throwable|null $previous
	 * 
	 * @return self
	 */
	public static function invalidType($value, array $expectedTypes, Throwable $previous = null)
	{
		return new static(sprintf('Excepted value of type %s, %s given', implode('|', $expectedTypes), is_object($value) ? get_class($value) : gettype($value)), 0, $previous);
	}
}
