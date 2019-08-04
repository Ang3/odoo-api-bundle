<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Handler;

use InvalidArgumentException;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Context;

/**
 * @author Joanis ROUANET
 */
class ManyToOneHandler implements SubscribingHandlerInterface
{
    /**
     * {@inherited}.
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => ManyToOne::class,
                'method' => 'serializeManyToOneToJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => ManyToOne::class,
                'method' => 'deserializeManyToOneFromJson',
            ],
        ];
    }

    /**
     * Serialize a many to one.
     *
     * @param JsonSerializationVisitor $visitor
     * @param object                   $object
     * @param array                    $type
     * @param SerializationContext     $context
     *
     * @throws InvalidArgumentException when the objet is not instance of class ManyToOne or RecordInterface
     *
     * @return int|false
     */
    public function serializeManyToOneToJson(JsonSerializationVisitor $visitor, object $object, array $type, SerializationContext $context)
    {
        // Si l'instance est une association simple
        if ($object instanceof ManyToOne) {
            // Récupération de la classe et de l'ID de l'association
            list($class, $id) = [
                $object->getClass(),
                $object->getId(),
            ];

            // Si pas de classe ou d'identifiant
            if (null === $class || null === $id) {
                // Retour négatif pour Odoo
                return false;
            }

            // Retour du tableau de la relation pour Odoo
            return $id;
        }

        // Si l'objet est un enregistrement
        if ($object instanceof RecordInterface) {
            // Récupération de l'ID de l'enregistrement
            $id = $object->getId();

            // Retour du tableau de la relation pour Odoo
            return $id ?: false;
        }

        throw new InvalidArgumentException(sprintf('Expected instance of class "%s" or "%s", "%s" given', ManyToOne::class, RecordInterface::class, get_class($object)));
    }

    /**
     * Deserialize a many to one.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param mixed                      $data
     * @param array                      $type
     * @param DeserializationContext     $context
     *
     * @return ManyToOne|null
     */
    public function deserializeManyToOneFromJson(JsonDeserializationVisitor $visitor, $data, array $type, DeserializationContext $context)
    {
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

        // Retour de l'association simple
        return ManyToOne::create(null, $id, $displayName);
    }
}
