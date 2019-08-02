<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Handler;

use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use Ang3\Bundle\OdooApiBundle\ORM\ModelRegistry;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Context;

/**
 * @author Joanis ROUANET
 */
class ManyToOneHandler implements SubscribingHandlerInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ModelRegistry
     */
    private $modelRegistry;

    /**
     * Constructor of the handler.
     *
     * @param Reader        $annotationReader
     * @param ModelRegistry $modelRegistry
     */
    public function __construct(Reader $annotationReader, ModelRegistry $modelRegistry)
    {
        $this->annotationReader = $annotationReader;
        $this->modelRegistry = $modelRegistry;
    }

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
                'method' => 'deserializeManyToOneToJson',
            ],
        ];
    }

    /**
     * Serialize a many to one.
     *
     * @param JsonSerializationVisitor $visitor
     * @param ManyToOne                $manyToOne
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array
     */
    public function serializeManyToOneToJson(JsonSerializationVisitor $visitor, ManyToOne $manyToOne, array $type, Context $context)
    {
        return $manyToOne->serialize();
    }

    /**
     * Deserialize a many to one.
     *
     * @param JsonDeserializationVisitor $visitor
     * @param bool|int|array             $params
     * @param array                      $type
     * @param Context                    $context
     *
     * @return ManyToOne|null
     */
    public function deserializeManyToOneToJson(JsonDeserializationVisitor $visitor, $params, array $type, Context $context)
    {
        // i on a pas un tableau de paramètres
        if (!is_array($params)) {
            // Si ce n'est pas non plus un entier
            if (!is_int($params)) {
                // Retour null
                return null;
            }

            // Initialisation des paramètres selon l'ID
            $params = [$params, null];
        }

        // Si on a moins de deux valeurs
        if (count($params) < 2) {
            // Pas d'ID
            return null;
        }

        // Récupération du modèle et de l'ID
        list($id, $displayName) = $params;

        // Retour de la relation
        return ManyToOne::create(null, $id, $displayName);
    }
}
