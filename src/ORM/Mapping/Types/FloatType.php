<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

/**
 * @author Joanis ROUANET
 */
class FloatType extends AbstractType
{
	/**
	 * {@inheritdoc}.
	 */
	public static function getName()
	{
		return 'float';
	}

	/**
	 * {@inheritdoc}.
	 *
	 * @return float|null
	 */
	public function convertToPhpValue($value, array $options = [])
	{
		return null !== $value ? $this->setDecimals((float) $value, $options) : null;
	}

	/**
	 * {@inheritdoc}.
	 *
	 * @return float|null
	 */
	public function convertToOdooValue($value, array $options = [])
	{
		return null !== $value ? $this->setDecimals((float) $value, $options) : null;
	}

	/**
	 * Set decimals to a float value.
	 *
	 * @internal
	 * 
	 * @param float $value
	 * @param array $options
	 *
	 * @return float
	 */
	private function setDecimals(float $value, array $options = [])
	{
		// Définition du nombre de décimales
		$decimals = !empty($options['decimals']) ? (int) $options['decimals'] : 0;

		// Si pas de décimales
		if($decimals < 1) {
			// Retour de la valeur inchangée
			return $value;
		}

		// Définition du facteur
		$factor = 10 * $decimals;

		// On définie les décimales via e facteur
		return (float) ((int) $value * $factor) / ($factor);
	}
}