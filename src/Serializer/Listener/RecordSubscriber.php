<?php

namespace Ang3\Bundle\OdooApiBundle\Serializer\Listener;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\Annotations;
use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\Model\AbstractRecord;
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
     * @var Reader
     */
    private $reader;

    /**
     * @var RecordManager
     */
    private $recordManager;

    /**
     * @param Reader        $reader
     * @param RecordManager $recordManager
     */
    public function __construct(Reader $reader, RecordManager $recordManager)
    {
        $this->reader = $reader;
        $this->recordManager = $recordManager;
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
                'class' => AbstractRecord::class
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

        // Récupération du visiteur de données
        $visitor = $event->getVisitor();

        // Si l'objet est bien l'instance d'un enregistrement
        if ($object instanceof RecordInterface) {
            // Création d'une réflection de la classe de l'objet
            $reflection = new ReflectionClass($object);

            // Récupération des propriété de la classe de l'enregistrement
            $properties = $reflection->getProperties();

            // Pour chaque propriété de l'objet
            foreach ($properties as $property) {
                // Récupération d'une association simple éventuelle
                $manyToOne = $this->recordManager->findManyToOneAssociation($property);

                // Si pas d'association simple
                if(null === $manyToOne) {
                    // Propriété suivante
                    continue;
                }

                // On rend accessible la propriété
                $property->setAccessible(true);

                // Récupération de la valeur  de la propriété
                $value = $property->getValue($object);

                // Si la propriété est déjà un enregistrement
                if($value instanceof RecordInterface) {
                    // Propriété suivante
                    continue;
                }

                // Si la valeur n'est pas une association simple
                if(!($value instanceof ManyToOne)) {
                    // On met la propriété à NULL
                    $property->setValue($object, null);

                    // Propriété suivante
                    continue;
                }

                // Enregistrement de la classe de l'association
                $value->setClass($manyToOne->class);

                // Récupération de l'ID de l'enregistrement cible de la relation
                $id = $value->getId();

                // Si pas d'identifiant
                if(null === $id) {
                    // Réinitialisation de la valeur
                    $property->setValue($object, null);

                    // Propriété suivante
                    continue;
                }

                // Création d'une réflection de la classe associée
                $targetReflection = new ReflectionClass($manyToOne->class);

                // Création de l'enregistrement
                $record = $targetReflection->newInstanceWithoutConstructor();

                // Assignation de l'ID de l'enregistrement
                $record->setId($id);

                // Récupération du nom affiché de la classe
                $displayName = $value->getDisplayName();

                // Si on a un nom affiché
                if(null !== $displayName && $targetReflection->hasProperty('displayName')) {
                    // Récupération de la propriété
                    $displayNameProperty = $targetReflection->getProperty('displayName');

                    // On rend accessible la propriété
                    $displayNameProperty->setAccessible(true);

                    // Récupération de la valeur de la propriété
                    $displayNameProperty->setValue($object, $displayName);
                }

                // Mise-à-jour ed la valeur par une instance d'enregistrement
                $property->setValue($object, $record);
            }
        }
    }
}
