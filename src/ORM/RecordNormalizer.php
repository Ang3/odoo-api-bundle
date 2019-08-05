<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\AssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToManyAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOneAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\MultipleAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\OneToManyAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\MultipleAssociation;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
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
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @param ArrayTransformerInterface $arrayTranformer
     * @param Reader                    $reader
     * @param ClassMetadataFactory      $classMetadataFactory
     */
    public function __construct(ArrayTransformerInterface $arrayTranformer, Reader $reader, ClassMetadataFactory $classMetadataFactory)
    {
        $this->arrayTranformer = $arrayTranformer;
        $this->reader = $reader;
        $this->classMetadataFactory = $classMetadataFactory;
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
        // Chargement des métadonnées de la classe
        $classMetadata = $this->classMetadataFactory->load($class);

        // Dénormlization de l'enregistrement
        $record = $this->arrayTranformer->fromArray($data, $class);

        // Pour chaque propriété d'association
        foreach ($classMetadata->iterateAssociations() as $property) {
            // Si c'est une association de type ManyToOne
            if ($property instanceof ManyToOneAssociationMetadata) {
                // Normalisation de la relation
                $this->denormalizeManyToOne($record, $property);

                // Propriété suivante
                continue;
            }

            // Si c'est une association de type ManyToMany
            if ($property instanceof ManyToManyAssociationMetadata) {
                // Normalisation de la relation
                $this->denormalizeManyToMany($record, $property);

                // Propriété suivante
                continue;
            }

            // Si c'est une association de type OneToMany
            if ($property instanceof OneToManyAssociationMetadata) {
                // Normalisation de la relation
                $this->denormalizeOneToMany($record, $property);

                // Propriété suivante
                continue;
            }
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
        $classMetadata = $this->classMetadataFactory->load($class);

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
            if ($classMetadata->hasProperty($propertyName)) {
                // Récupération de la réflection de la propriété
                $property = $classMetadata->getProperty($propertyName);

                // Si la propriété n'est pas une association
                if (!($property instanceof AssociationMetadata)) {
                    // Champ suivant
                    continue;
                }

                // Changement de classe courante
                $classMetadata = $this->classMetadataFactory->load($property->getTargetClass());

                // Mise-à-jour des nom de champs sérialisés selon la nouvelle classe
                $serializedNames = $this->getSerializedNames($property->getTargetClass());
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
        return $this
            ->getClassMetadata($class)
            ->getSerializedNames()
        ;
    }

    /**
     * Get metadata of a class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getClassMetadata(string $class)
    {
        return $this->classMetadataFactory->load($class);
    }

    /**
     * Denormalize ManyToOne association of property.
     *
     * @param RecordInterface              $record
     * @param ManyToOneAssociationMetadata $property
     *
     * @throws InvalidArgumentException when the value of an association property is not valid
     */
    public function denormalizeManyToOne(RecordInterface $record, ManyToOneAssociationMetadata $property)
    {
        // Récupération de la valeur  de la propriété
        $value = $property->getValue($record);

        // Si pas de valeur
        if (false === $value || null === $value) {
            // Mise-à-jour ed la valeur par une instance d'enregistrement
            $property->setValue($record, null);

            // Fin des traitements
            return;
        }

        // Si la propriété est déjà un enregistrement
        if ($value instanceof RecordInterface) {
            // Fin des traitements
            return;
        }

        // Si la valeur n'est pas une association simple
        if (!($value instanceof SingleAssociation)) {
            // On met la propriété à NULL
            $property->setValue($record, null);

            // Fin des traitements
            return;
        }

        // Création d'une réflection de la classe associée
        $targetReflection = new ReflectionClass($property->getTargetClass());

        // Création de l'enregistrement
        $targetRecord = $targetReflection->newInstanceWithoutConstructor();

        // Assignation de l'ID de l'enregistrement
        $this->setRecordId($targetRecord, $value->getId());

        // Si on a un nom affiché
        if (null !== $value->getDisplayName() && $targetReflection->hasProperty('displayName')) {
            // Récupération de la propriété
            $displayNameProperty = $targetReflection->getProperty('displayName');

            // On rend accessible la propriété
            $displayNameProperty->setAccessible(true);

            // Récupération de la valeur de la propriété
            $displayNameProperty->setValue($targetRecord, $value->getDisplayName());
        }

        // Mise-à-jour de la valeur par l'instance de l'enregistrement
        $property->setValue($record, $targetRecord);
    }

    /**
     * Denormalize ManyToMany association of property.
     *
     * @param RecordInterface               $record
     * @param ManyToManyAssociationMetadata $property
     */
    public function denormalizeManyToMany(RecordInterface $record, ManyToManyAssociationMetadata $property)
    {
        return $this->denormalizeToManyAssociation($record, $property);
    }

    /**
     * Denormalize ManyToMany association of property.
     *
     * @param RecordInterface              $record
     * @param OneToManyAssociationMetadata $property
     */
    public function denormalizeOneToMany(RecordInterface $record, OneToManyAssociationMetadata $property)
    {
        return $this->denormalizeToManyAssociation($record, $property);
    }

    /**
     * Denormalize ManyToMany association of property.
     *
     * @internal
     *
     * @param RecordInterface             $record
     * @param MultipleAssociationMetadata $property
     */
    private function denormalizeToManyAssociation(RecordInterface $record, MultipleAssociationMetadata $property)
    {
        // Récupération de la valeur  de la propriété
        $value = $property->getValue($record);

        // Si la propriété est déjà un enregistrement
        if ($value instanceof RecordInterface) {
            // On met la propriété dans un tableau
            $property->setValue($record, [$value]);

            // Fin des traitements
            return;
        }

        // Si la valeur n'est pas une association simple
        if (!($value instanceof MultipleAssociation)) {
            // On met la propriété à NULL
            $property->setValue($record, null);

            // Fin des traitements
            return;
        }

        // Initialisation des enregistrements
        $records = [];

        // Création d'une réflection de la classe associée
        $targetReflection = new ReflectionClass($property->getTargetClass());

        // Pour chaque identifiant de la relation
        foreach (array_unique($value->getIds()) as $id) {
            // Création de l'enregistrement
            $targetRecord = $targetReflection->newInstanceWithoutConstructor();

            // Assignation de l'ID de l'enregistrement
            $this->setRecordId($targetRecord, $id);

            // Enregistrement de l'enregistrement dans la collection
            $records[] = $targetRecord;
        }

        // Mise-à-jour ed la valeur par la collection d'enregistrements
        $property->setValue($record, $records);
    }

    /**
     * Set ID to a record.
     *
     * @param RecordInterface $record
     * @param int|null        $id
     */
    public function setRecordId(RecordInterface $record, int $id = null)
    {
        // Réfléction de la classe de l'enregistrement
        $reflection = new ReflectionClass($record);

        // Si l'enregistrement ne possède pas de propriété d'identifiant
        if (!$reflection->hasProperty('id')) {
            throw new InvalidArgumentException(sprintf('Missing property "id" in record class "%s"', get_class($record)));
        }

        // Récupération de la propriété de l'identifiant
        $property = $reflection->getProperty('id');

        // On rend accessible la propriété
        $property->setAccessible(true);

        // Enregistrement de l'identifiant au sein de l'enregistrement
        $property->setValue($record, $id);
    }
}
