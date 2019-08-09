<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Types;

/**
 * @author Joanis ROUANET
 */
class IntegerType extends AbstractType
{
	/**
	 * {@inheritdoc}.
	 */
	public static function getName()
	{
		return 'integer';
	}

	/**
	 * {@inheritdoc}.
	 *
	 * @return int|null
	 */
	public function convertToPhpValue($value, array $options = [])
	{
		return null !== $value ? (int) $value : null;
	}

	/**
	 * {@inheritdoc}.
	 *
	 * @return int|null
	 */
	public function convertToOdooValue($value, array $options = [])
	{
		return null !== $value ? (int) $value : null;
	}
}