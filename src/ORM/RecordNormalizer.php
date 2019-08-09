<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

/**
 * @author Joanis ROUANET
 */
class RecordNormalizer
{
	/**
	 * @var RecordManager
	 */
	private $recordManager;

	/**
	 * @param RecordManager $recordManager
	 */
	public function __construct(RecordManager $recordManager)
	{
		$this->recordManager = $recordManager;
	}

	/**
	 * Normalize record into array.
	 * 
	 * @param  RecordInterface      $record
	 * @param  NormalizationContext $context
	 * 
	 * @return array
	 */
	public function toArray(RecordInterface $record, NormalizationContext $context)
	{
		// Récupération des métadonnées de l'enregistrement
		$classMetadata = $this->recordManager->getClassMetadata($record);

		// Initialisation des données normalisées
		$data = [];

		// Pour chaque propriété de l'enregistrement
		foreach($classMetadata->iterateProperties() as $property) {
			// Si la propriété représente un champ simple
			if($property->isField()) {
				// ...
				
				// Propriété suivante
				continue;
			}

			// Si la propriété représente une association
			if($property->isAssociation()) {
				// ...
				
				// Propriété suivante
				continue;
			}
		}

		// Retour des données normalisées
		return $data;
	}

	/**
	 * Denormalize record from array.
	 * 
	 * @param  RecordInterface      $record
	 * @param  string               $class
	 * @param  NormalizationContext $context
	 * 
	 * @return RecordInterface
	 */
	public function fromArray(RecordInterface $record, string $class, NormalizationContext $context)
	{
		// ...
	}
}