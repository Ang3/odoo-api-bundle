<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Handler;

use InvalidArgumentException;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToMany;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOne;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\SerializationContext;

/**
 * @author Joanis ROUANET
 */
class ManyToManyHandler implements SubscribingHandlerInterface
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
                'type' => ManyToMany::class,
                'method' => 'serializeManyToManyToJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => ManyToMany::class,
                'method' => 'deserializeManyToManyFromJson',
            ],
        ];
    }

    /**
     * Serialize a many to one.
     *
     * @param JsonSerializationVisitor $visitor
     * @param mixed                    $data
     * @param array                    $type
     * @param SerializationContext     $context
     *
     * @thvalues InvalidArgumentException when the objet is not instance of class ManyToMany or RecordInterface
     *
     * @return array
     */
    public function serializeManyToManyToJson(JsonSerializationVisitor $visitor, $data, array $type, SerializationContext $context)
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
            if ($value instanceof ManyToOne) {
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
        }

        // Retour des identifiants
        return $ids;
    }

    /**
     * Deserialize a many to one.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param array                      $ids
     * @param array                      $type
     * @param DeserializationContext     $context
     *
     * @return ManyToMany|null
     */
    public function deserializeManyToManyFromJson(JsonDeserializationVisitor $visitor, array $ids, array $type, DeserializationContext $context)
    {
        return ManyToMany::create(null, $ids);
    }
}
