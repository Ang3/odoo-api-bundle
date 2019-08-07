<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Serializer\Handler;

use InvalidArgumentException;
use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\ORM\Registry;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\MultipleAssociation;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\SerializationContext;

/**
 * @author Joanis ROUANET
 */
class RecordAssociationHandler
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Constructor of registry.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Serialize a single association.
     *
     * @param JsonSerializationVisitor $visitor
     * @param mixed                    $data
     * @param array                    $type
     * @param SerializationContext     $context
     *
     * @throws InvalidArgumentException when the type of data is not valid
     *
     * @return int|false
     */
    public function serializeSingleAssociationToJson(JsonSerializationVisitor $visitor, $data, array $type, SerializationContext $context)
    {
        // Si l'objet est un enregistrement une association simple
        if ($data instanceof RecordInterface || $data instanceof SingleAssociation) {
            // Récupération de l'ID de l'enregistrement
            $id = $data->getId();

            // Retour du tableau de la relation pour Odoo
            return $id ?: false;
        }

        // Si pas d'entier
        if (!is_int($data)) {
            throw new InvalidArgumentException(sprintf('Excepted value of type "integer" or either instance of "%s" or "%s", "%s" given', RecordInterface::class, SingleAssociation::class, gettype($data)));
        }

        // Retour de l'identifiant
        return $data;
    }

    /**
     * Deserialize a single association.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param mixed                      $data
     * @param array                      $type
     * @param DeserializationContext     $context
     *
     * @throws InvalidArgumentException when the target class is not mapped
     *
     * @return RecordInterface|SingleAssociation|null
     */
    public function deserializeSingleAssociationFromJson(JsonDeserializationVisitor $visitor, $data, array $type, DeserializationContext $context)
    {
        // Récupération des paramètres
        $params = $type['params'];

        // Si pas de données
        if (null === $data || false === $data) {
            // Retour nulle
            return null;
        }

        // Si on a un entier
        if (is_int($data)) {
            // Création de l'association simple
            $association = new SingleAssociation($data);
        } else {
            // Si on a pas un tableau
            if (!is_array($data)) {
                // Retour nul
                return null;
            }

            // Récupération de l'identifiant
            list($id, $displayName) = [
                !empty($data[0]) ? $data[0] : null,
                !empty($data[1]) ? $data[1] : null,
            ];

            // Si pas d'identifiant
            if (null === $id) {
                // Retour nul
                return null;
            }

            // Création de l'association simple
            $association = new SingleAssociation($id, $displayName);
        }

        // Si pas de classe cible
        if (!isset($params[0])) {
            // Retour de l'association
            return $association;
        }

        // Récupération de la classe cible
        $targetClass = $params[0];

        // Récupération du manager des enregistrement concerné
        $recordManager = $this->getRecordManager(isset($params[1]) ? $params[1] : null);

        // Si la classe n'est pas mappée
        if (!$recordManager->getCatalog()->hasClass($targetClass)) {
            throw new InvalidArgumentException(sprintf('The target class "%s" of single association is not mapped.', $targetClass));
        }

        // Création d'une réflection de la classe associée
        $targetReflection = new ReflectionClass($targetClass);

        // Création de l'enregistrement
        $targetRecord = $targetReflection->newInstanceWithoutConstructor();

        // Assignation de l'ID de l'enregistrement
        $recordManager->getNormalizer()->setRecordId($targetRecord, $association->getId());

        // Si on a un nom affiché
        if (null !== $association->getDisplayName() && $targetReflection->hasProperty('displayName')) {
            // Récupération de la propriété
            $displayNameProperty = $targetReflection->getProperty('displayName');

            // On rend accessible la propriété
            $displayNameProperty->setAccessible(true);

            // Récupération de la valeur de la propriété
            $displayNameProperty->setValue($targetRecord, $association->getDisplayName());
        }

        // Retour du dossier
        return $targetRecord;
    }

    /**
     * Serialize a multiple association.
     *
     * @param JsonSerializationVisitor $visitor
     * @param mixed                    $data
     * @param array                    $type
     * @param SerializationContext     $context
     *
     * @return array
     */
    public function serializeMultipleAssociationToJson(JsonSerializationVisitor $visitor, $data, array $type, SerializationContext $context)
    {
        // Initialisation des identifiants
        $ids = [];

        // Conversion des données en tableau
        $data = (array) $data;

        // Pour chaque valeur du tableau
        foreach ($data as $value) {
            // Si la valeur n'est pas un objet
            if (!is_object($value)) {
                // Récupération de la valeur entière
                $id = intval($value);

                // Si la valeur est positive
                if (0 < $id) {
                    // Enregistrement de la valeur dans les ID
                    $ids[] = (int) $value;
                }

                // Valeur suivante
                continue;
            }

            // Si la valeur est une interface d'enregistrement
            if ($value instanceof RecordInterface) {
                // Récupération de l'ID de l'enregistrement
                $id = $value->getId();

                // Si on a un identifiant
                if (null !== $id) {
                    // Enregistrement de l'ID dans les ID
                    $ids[] = $id;
                }

                // Valeur suivante
                continue;
            }

            // Si la valeur est une association simple
            if ($value instanceof MultipleAssociation) {
                // Pour chaque identifiant de la relation
                foreach ($value->getIds() as $id) {
                    // Enregistrement de l'ID dans la liste
                    $ids[] = $id;
                }
            }
        }

        // Retour des identifiants
        return array_unique($ids);
    }

    /**
     * Deserialize a multiple association.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param mixed                      $data
     * @param array                      $type
     * @param DeserializationContext     $context
     *
     * @return RecordInterface[]|MultipleAssociation|null
     */
    public function deserializeMultipleAssociationFromJson(JsonDeserializationVisitor $visitor, $data, array $type, DeserializationContext $context)
    {
        // Récupération des paramètres
        $params = $type['params'];

        // Formatage des données en tableau
        $data = is_array($data) ? $data : [];

        // Création de l'association multiple
        $association = new MultipleAssociation($data);

        // Si pas de classe cible
        if (!isset($params[0])) {
            // Retour de l'association
            return $association;
        }

        // Récupération de la classe cible
        $targetClass = $params[0];

        // Récupération du manager des enregistrement concerné
        $recordManager = $this->getRecordManager(isset($params[1]) ? $params[1] : null);

        // Si la classe n'est pas mappée
        if (!$recordManager->getCatalog()->hasClass($targetClass)) {
            throw new InvalidArgumentException(sprintf('The target class "%s" of single association is not mapped.', $targetClass));
        }

        // Initialisation des enregistrements
        $records = [];

        // Création d'une réflection de la classe associée
        $targetReflection = new ReflectionClass($targetClass);

        // Pour chaque identifiant de la relation
        foreach (array_unique($association->getIds()) as $id) {
            // Création de l'enregistrement
            $targetRecord = $targetReflection->newInstanceWithoutConstructor();

            // Assignation de l'ID de l'enregistrement
            $recordManager->getNormalizer()->setRecordId($targetRecord, $id);

            // Enregistrement de l'enregistrement dans la collection
            $records[] = $targetRecord;
        }

        // Retour des enregistrements
        return $records;
    }

    /**
     * Get a record manager by name.
     *
     * @param string|null $name
     *
     * @return RecordManager
     */
    public function getRecordManager(string $name = null)
    {
        return $this->registry->get($name);
    }
}
