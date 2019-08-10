<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\AssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;

/**
 * @author Joanis ROUANET
 */
class Normalizer
{
    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @param ClassMetadataFactory $classMetadataFactory
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * Normalize object into array.
     *
     * @param object                    $object
     * @param NormalizationContext|null $context
     *
     * @return array
     */
    public function toArray(object $object, NormalizationContext $context = null)
    {
        // Récupération des métadonnées de l'objet
        $classMetadata = $this->classMetadataFactory->load($object);

        // Initialisation des données normalisées
        $data = [];

        // Pour chaque propriété de l'enregistrement
        foreach ($classMetadata->iterateProperties() as $property) {
            // Si la propriété représente un champ simple
            if ($property->isField()) {
                // ...

                // Propriété suivante
                continue;
            }

            // Si la propriété représente une association
            if ($property->isAssociation()) {
                // ...

                // Propriété suivante
                continue;
            }
        }

        // Retour des données normalisées
        return $data;
    }

    /**
     * Denormalize object from array.
     *
     * @param array                     $data
     * @param string                    $class
     * @param NormalizationContext|null $context
     *
     * @return object
     */
    public function fromArray(array $data = [], string $class, NormalizationContext $context = null)
    {
        // Récupération des métadonnées de la classe
        $classMetadata = $this->classMetadataFactory->load($class);

        // ...
    }

    /**
     * Normalize domains criteria names by Odoo model names.
     *
     * @param ClassMetadata $classMetadata
     * @param array         $domains
     *
     * @return array
     */
    public function normalizeDomains(ClassMetadata $classMetadata, array $domains = [])
    {
        // Pour chaque domaine
        foreach ($domains as $key => &$criteria) {
            // Si on a un tableau (donc un critère)
            if (is_array($criteria) && !empty($criteria[0])) {
                // Noramlisation du nom du champ cible du critère
                $criteria[0] = $this->normalizeFieldName($classMetadata, $criteria[0]);
            }
        }

        // Retour des domaines
        return $domains;
    }

    /**
     * Normalize a flattened field name.
     *
     * @param ClassMetadata $classMetadata
     * @param string        $fieldName
     *
     * @return string
     */
    public function normalizeFieldName(ClassMetadata $classMetadata, string $fieldName)
    {
        // Récupération des champs par explosion selon les points
        $fields = explode('.', $fieldName);

        // Pour chaque champ à traverser
        foreach ($fields as $key => &$field) {
            // Récupération de la propriété
            $property = $classMetadata->getProperty($field);

            // Mise-à-jour du champ par le nom distant
            $field = $property->getRemoteName();

            // Si la classe possède la propriété
            if ($property instanceof AssociationMetadata) {
                // Changement de classe courante
                $classMetadata = $this->classMetadataFactory->load($property->getTargetClass());
            }
        }

        // Retour du champ aplati normalisé
        return implode('.', $fields);
    }
}
