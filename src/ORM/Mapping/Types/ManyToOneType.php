<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Types;

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
		// Si la valeur est un entier
		if(is_int($value)) {
			// Retour de la valeur
			return $value;
		}

		// Si la valeur est l'interface d'un enregistrement
		if($value instanceof RecordInterface) {
			// Retour de la valeur de l'identifiant
			return $value->getId();
		}

		throw new MappingTypeException::invalidType($value, ['integer', RecordInterface::class]);
	}
}