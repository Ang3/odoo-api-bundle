<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\AssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOneMetadata;

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
            // Si la propriété est en lecture seule
            if ($property->isReadOnly()) {
                // Propriété suivante
                continue;
            }

            // Récupération de la valeur de la propriété
            $value = $classMetadata->getValue($object, $property);

            // Convertion de la valeur pour Odoo
            $value = $property
                ->getType()
                ->convertToOdooValue($value, $property->getOptions())
            ;

            // Si la valeur de la propriété ne peut pas être nulle et que c'est le cas
            if (!$property->isNullable() && null === $value) {
                // On ingore la propriété
                continue;
            }

            // Enregistrement de la valeur de la propriété Odoo dans les données
            $data[$property->getRemoteName()] = $value;
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

        // Création d'une instance de la classe de l'objet à dénormaliser
        $object = $classMetadata->newInstance();

        // Pour chaque valeur des données soumises
        foreach ($data as $remoteName => $value) {
            // Résolution éventuelle de la propriété de la classe
            $property = $classMetadata->resolveMapped($remoteName);

            // Si la propriété est nulle
            if (null === $property) {
                // Valeur suivante
                continue;
            }

            // Récupération de la valeur après typage
            $value = $property
                ->getType()
                ->convertToPhpValue($value, $property->getOptions())
            ;

            // Si la propriété est une association
            if ($property instanceof AssociationMetadata) {
                // Récupération de la classe cible
                $targetClass = $property->getTargetClass();

                // Si c'est une association simple
                if ($property instanceof ManyToOneMetadata) {
                    // Dénormalisation
                    $value = $this->fromArray([
                        'id' => $value[0],
                        'display_name' => $value[1],
                    ], $targetClass, $context);
                } else {
                    // Initialisation de la collection
                    $collection = [];

                    // Pour ligne de la valeur
                    foreach ($value as $id) {
                        // Si on a déjà transformer cet ID
                        if (isset($collection[$id])) {
                            // Ligne suivante
                            continue;
                        }

                        // Dénormalisation
                        $collection[$id] = $this->fromArray([
                            'id' => $id,
                        ], $targetClass, $context);
                    }

                    // Récupération des valeurs uniquement
                    $value = array_values($collection);
                }
            }

            // Enregistrement de la valeur au sein de l'objet
            $classMetadata->setValue($object, $property, $value);
        }

        // Retour de l'objet
        return $object;
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
