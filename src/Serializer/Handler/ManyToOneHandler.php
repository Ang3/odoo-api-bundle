<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Handler;

use InvalidArgumentException;
use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\Model\AbstractRecord;
use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\ModelRegistry;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Doctrine\Common\Annotations\Reader;
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
     * @param ManyToOne                $manyToOne
     * @param array                    $type
     * @param SerializationContext     $context
     *
     * @return array|bool|null
     */
    public function serializeManyToOneToJson(JsonSerializationVisitor $visitor, ManyToOne $manyToOne, array $type, SerializationContext $context)
    {
        // Récupération de la classe et de l'ID de l'association
        list($class, $id) = [
            $manyToOne->getClass(),
            $manyToOne->getId()
        ];

        // Si pas de classe ou d'identifiant
        if(null === $class || null === $id) {
            // Retour négatif pour Odoo
            return false;
        }

        // Retour du tableau de la relation pour Odoo
        return [ $id, $manyToOne->getDisplayName() ];
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
        if(!is_array($data)) {
            // Retour nul
            return null;
        }

        // Récupération de l'identifiant
        list($id, $displayName) = [
            !empty($data[0]) ? $data[0] : null,
            !empty($data[1]) ? $data[1] : null,
        ];

        // Si pas d'identifiant
        if(null === $id) {
            // Retour nul
            return null;
        }

        // Retour de l'association simple
        return ManyToOne::create(null, $id, $displayName);
    }
}