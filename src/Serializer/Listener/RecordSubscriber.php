<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Listener;

use ReflectionClass;
use ReflectionProperty;
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
                // On rend accessible la propriété
                $property->setAccessible(true);

                // Récupératio de la valeur de la propriété
                $value = $property->getValue($object);

                // Si la valeur est une association simple
                if ($value instanceof ManyToOne) {
                    // Enregistrement de la classe source
                    $this
                        ->setPropertyValue($value, 'class', $reflection->getName())
                        ->setPropertyValue($value, 'property', $property->getName())
                    ;

                    /**
                     * Récupération de l'annotation de l'association.
                     *
                     * @var Annotations\ManyToOne|null
                     */
                    $manyToOne = $this->reader->getPropertyAnnotation($property, Annotations\ManyToOne::class);

                    // Si on a une annotation
                    if (null !== $manyToOne) {
                        // Enregistrement de la classe cible
                        $this->setPropertyValue($value, 'target', $manyToOne->class);
                    }
                }
            }
        }
    }

    /**
     * Set property value on object.
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     */
    private function setPropertyValue(object $object, string $property, $value)
    {
        // Récupération de la propriété de la classe de l'association
        $property = new ReflectionProperty(get_class($object), $property);

        // On rend accessible la propriété
        $property->setAccessible(true);

        // Enregistrement de la classe source
        $property->setValue($object, $value);

        // Retour du subscriber pour le chainage
        return $this;
    }
}
