<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingTypeException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;

/**
 * @author Joanis ROUANET
 */
class ManyToOneType extends AbstractType
{
	/**
	 * {@inheritdoc}.
	 */
	public static function getName()
	{
		return 'many_to_one';
	}

	/**
	 * {@inheritdoc}.
	 *
	 * @return int|false
	 */
	public function convertToOdooValue($value, array $options = [])
	{
		// Si pas de valeur
		if(null === $value) {
			// Retour négatif
			return false;
		}

		// Si la valeur est un entier
		if(is_int($value)) {
			// Retour de la valeur
			return $value;
		}

		// Si la valeur est un tableau
		if(is_array($value)) {
			// Si pas de valeur
			if(0 === count($value)) {
				// Retour négatif
				return false;
			}

			// Relevé de la première valeur
			$firstValue = array_shift($value);

			// Retour de l'identifiant éventuel
			return null !== $firstValue ? (int) $firstValue : null;
		}

		// Si la valeur est l'interface d'un enregistrement
		if($value instanceof RecordInterface) {
			// Retour de la valeur de l'identifiant
			return $value->getId();
		}

		throw MappingTypeException::invalidType($value, ['integer', 'array', RecordInterface::class]);
	}
}