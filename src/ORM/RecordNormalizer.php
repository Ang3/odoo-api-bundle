<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use Ang3\Bundle\OdooApiBundle\Annotations;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;

/**
 * @author Joanis ROUANET
 */
class RecordNormalizer
{
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTranformer;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * Constructor of the manager.
     *
     * @param ArrayTransformerInterface $arrayTranformer
     * @param Reader                    $reader
     */
    public function __construct(ArrayTransformerInterface $arrayTranformer, Reader $reader)
    {
        $this->arrayTranformer = $arrayTranformer;
        $this->reader = $reader;
    }

    /**
     * Denormalize a record from Odoo data.
     *
     * @param array  $data
     * @param string $class
     *
     * @throws Exception when the record class is not valid
     *
     * @return RecordInterface
     */
    public function denormalize(array $data, string $class)
    {
        // Dénormlization de l'enregistrement
        $record = $this->arrayTranformer->fromArray($data, $class);

        // Création d'une réflection de la classe de l'objet
        $reflection = new ReflectionClass($record);

        // Récupération des propriété de la classe de l'enregistrement
        $properties = $reflection->getProperties();

        // Pour chaque propriété de l'objet
        foreach ($properties as $property) {
            // Récupération d'une association simple éventuelle
            $manyToOne = $this->findManyToOneAssociation($property);

            // Si pas d'association simple
            if (null === $manyToOne) {
                // Propriété suivante
                continue;
            }

            // On rend accessible la propriété
            $property->setAccessible(true);

            // Récupération de la valeur  de la propriété
            $value = $property->getValue($record);

            // Si la propriété est déjà un enregistrement
            if ($value instanceof RecordInterface) {
                // Propriété suivante
                continue;
            }

            // Si la valeur n'est pas une association simple
            if (!($value instanceof ManyToOne)) {
                // On met la propriété à NULL
                $property->setValue($record, null);

                // Propriété suivante
                continue;
            }

            // Enregistrement de la classe de l'association
            $value->setClass($manyToOne->class);

            // Récupération de l'ID de l'enregistrement cible de la relation
            $id = $value->getId();

            // Si pas d'identifiant
            if (null === $id) {
                // Réinitialisation de la valeur
                $property->setValue($record, null);

                // Propriété suivante
                continue;
            }

            // Création d'une réflection de la classe associée
            $targetReflection = new ReflectionClass($manyToOne->class);

            // Création de l'enregistrement
            $targetRecord = $targetReflection->newInstanceWithoutConstructor();

            // Assignation de l'ID de l'enregistrement
            $targetRecord->setId($id);

            // Récupération du nom affiché de la classe
            $displayName = $value->getDisplayName();

            // Si on a un nom affiché
            if (null !== $displayName && $targetReflection->hasProperty('displayName')) {
                // Récupération de la propriété
                $displayNameProperty = $targetReflection->getProperty('displayName');

                // On rend accessible la propriété
                $displayNameProperty->setAccessible(true);

                // Récupération de la valeur de la propriété
                $displayNameProperty->setValue($targetRecord, $displayName);
            }

            // Mise-à-jour ed la valeur par une instance d'enregistrement
            $property->setValue($record, $targetRecord);
        }

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Normalize a record.
     *
     * @param RecordInterface $record
     *
     * @return array
     */
    public function normalize(RecordInterface $record)
    {
        // Création du context de sérialisation
        $context = SerializationContext::create();

        // On sérialise aussi les valeurs nulles
        $context->setSerializeNull(true);

        // Retour de la normalisation
        return $this->arrayTranformer->toArray($record, $context);
    }

    /**
     * Normalize domains criteria names by Odoo model names.
     *
     * @param string $class
     * @param array  $domains
     *
     * @return array
     */
    public function normalizeDomains(string $class, array $domains = [])
    {
        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->getSerializedNames($class);

        // Pour chaque domaine
        foreach ($domains as $key => &$criteria) {
            // Si on a un tableau (donc un critère)
            if (is_array($criteria) && !empty($criteria[0])) {
                // Noramlisation du nom du champ cible du critère
                $criteria[0] = $this->normalizeFieldName($class, $criteria[0]);
            }
        }

        // Retour des domaines
        return $domains;
    }

    /**
     * Normalize a flattened field name.
     *
     * @param string $class
     * @param string $fieldName
     *
     * @return string
     */
    public function normalizeFieldName(string $class, string $fieldName)
    {
        // Réflection de la classe
        $reflection = new ReflectionClass($class);

        // Récupération des champs par explosion selon les points
        $fields = explode('.', $fieldName);

        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->getSerializedNames($class);

        // Pour chaque champ à traverser
        foreach ($fields as $key => &$field) {
            // Récupération du nom de la propriété
            $propertyName = $field;

            // Mise-à-jour du nom du champ
            $field = array_key_exists($field, $serializedNames) ? $serializedNames[$field] : $field;

            // Si la classe possède la propriété
            if ($reflection->hasProperty($propertyName)) {
                // Récupération de la réflection de la propriété
                $property = $reflection->getProperty($propertyName);

                // Récupération éventuelle d'une annotation de relation simple
                $manyToOne = $this->findManyToOneAssociation($property);

                // Si pas d'annotation
                if (null === $manyToOne) {
                    // Champ suivant
                    continue;
                }

                // Changement de classe courante
                $reflection = new ReflectionClass($manyToOne->class);

                // Mise-à-jour des nom de champs sérialisés selon la nouvelle classe
                $serializedNames = $this->getSerializedNames($manyToOne->class);
            }
        }

        // Retour du champ aplati normalisé
        return implode('.', $fields);
    }

    /**
     * Get serialized names of class properties by property name.
     *
     * @param string $class
     *
     * @return array
     */
    public function getSerializedNames(string $class)
    {
        // Réflection de la classe
        $reflection = new ReflectionClass($class);

        // Récupération des propriétés de la classe
        $properties = $reflection->getProperties();

        // Initialisation des noms de propriété sérialisés
        $serializedNames = [];

        // Pour chaque propriété
        foreach ($properties as $property) {
            /**
             * Récupération d'une annotation éventuelle du nom sérialisé de la propriété.
             *
             * @var JMS\SerializedName|null
             */
            $annotation = $this->reader->getPropertyAnnotation($property, JMS\SerializedName::class);

            // Si pas d'annotation
            if (null === $annotation) {
                // Enregistrement du nom sérialisé de la propriété par rapport à son nom
                $serializedNames[$property->getName()] = $property->getName();

                // Propriété suivante
                continue;
            }

            // Enregistrement du nom sérialisé de la propriété par rapport à son nom
            $serializedNames[$property->getName()] = $annotation->name;
        }

        // Retour des noms de propriété sérialisés
        return $serializedNames;
    }

    /**
     * Find ManyToOne association on property.
     *
     * @param ReflectionProperty $property
     *
     * @return Annotations\ManyToOne|null
     */
    public function findManyToOneAssociation(ReflectionProperty $property)
    {
        /** @var Annotations\ManyToOne|null */
        $manyToOne = $this->reader->getPropertyAnnotation($property, Annotations\ManyToOne::class);

        // Retour de l'association éventuelle
        return $manyToOne;
    }
}
