<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Listener;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\Annotations;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;

/**
 * @author Joanis ROUANET
 */
class RecordSubscriber implements EventSubscriberInterface
{
    /**
     * @var RecordManager
     */
    private $recordManager;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * Constructor of the record.
     *
     * @param RecordManager $recordManager
     * @param Reader        $reader
     */
    public function __construct(RecordManager $recordManager, Reader $reader)
    {
        $this->recordManager = $recordManager;
        $this->reader = $reader;
    }

    /**
     * {@inherited}.
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_DESERIALIZE,
                'method' => 'onPostDeserialize',
            ],
        ];
    }

    /**
     * On post deserialize event.
     *
     * @param ObjectEvent $event
     */
    public function onPostDeserialize(ObjectEvent $event)
    {
        // Récupération de l'objet désérialisé
        $object = $event->getObject();

        // Si l'objet est bien l'instance d'un enregistrement
        if ($object instanceof RecordInterface) {
            // Réflection de la classe
            $reflection = new ReflectionClass(get_class($object));

            // Pour chaque propriété de la classe
            foreach ($reflection->getProperties() as $property) {
                /**
                 * Récupération de l'annotation de l'association.
                 *
                 * @var Annotations\ManyToOne|null
                 */
                $manyToOne = $this->reader->getPropertyAnnotation($property, Annotations\ManyToOne::class);

                // Si on a une annotation de relation
                if (null === $manyToOne) {
                    // Propriété suivante
                    continue;
                }

                // On rend accessible la propriété
                $property->setAccessible(true);

                // Récupératio de la valeur de la propriété
                $value = $property->getValue($object);

                // Si la valeur n'est pas une relation
                if (!($value instanceof ManyToOne)) {
                    // Propriété suivante
                    continue;
                }

                // Si la cible est nulle
                if (null === $value->getClass()) {
                    // Assignation de la classe cible de la relation
                    $value->setClass($manyToOne->class);
                }
            }
        }
    }
}
