<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Types;

/**
 * @abstract
 * 
 * @author Joanis ROUANET
 */
abstract class AbstractType implements TypeInterface
{
	/**
	 * {@inheritdoc}.
	 */
	public function convertToPhpValue($value, array $options = [])
	{
		return $value;
	}

	/**
	 * {@inheritdoc}.
	 */
	public function convertToOdooValue($value, array $options = [])
	{
		return $value;
	}
}