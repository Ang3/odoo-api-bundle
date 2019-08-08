<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Serializer;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
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

        // Retour de la dénormalization de l'enregistrement
        return $this->arrayTranformer->fromArray($data, $class);
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
            if ($classMetadata->hasAssociation($propertyName)) {
                // Récupération de l'association
                $association = $classMetadata->getAssociation($propertyName);

                // Changement de classe courante
                $classMetadata = $this->classMetadataFactory->load($association->getTargetClass());

                // Mise-à-jour des nom de champs sérialisés selon la nouvelle classe
                $serializedNames = $this->getSerializedNames($association->getTargetClass());
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
            throw new InvalidArgumentException(sprintf('Missing property "id" in record class "%s"', ClassUtils::getClass($record)));
        }

        // Récupération de la propriété de l'identifiant
        $property = $reflection->getProperty('id');

        // On rend accessible la propriété
        $property->setAccessible(true);

        // Enregistrement de l'identifiant au sein de l'enregistrement
        $property->setValue($record, $id);
    }
}
