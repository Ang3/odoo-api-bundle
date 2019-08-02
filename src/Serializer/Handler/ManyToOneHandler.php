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
     * @param bool|array                 $params
     * @param array                      $type
     * @param Context                    $context
     *
     * @return ManyToOne|null
     */
    public function deserializeManyToOneToJson(JsonDeserializationVisitor $visitor, $params, array $type, Context $context)
    {
        // Définition des paramètres
        $params = !is_array($params) ? [null, ''] : $params;

        // Récupération de l'objet sérialisé
        $object = $visitor->getCurrentObject();

        // Retour de la relation
        return new ManyToOne();
    }
}
