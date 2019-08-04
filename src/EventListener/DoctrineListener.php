<?php

namespace Ang3\Bundle\OdooApiBundle\EventListener;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\RecordNormalizer;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Joanis ROUANET
 */
class DoctrineListener
{
    /**
     * @var RecordNormalizer
     */
    private $recordNormalizer;

    /**
     * @param RecordNormalizer $recordNormalizer
     */
    public function __construct(RecordNormalizer $recordNormalizer)
    {
        $this->recordNormalizer = $recordNormalizer;
    }

    /**
     * On postLoad event.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        // Récupération de l'entité
        $entity = $eventArgs->getEntity();

        // Récupération des métadonnées de la classe
        $classMetadata = $eventArgs->getEntityManager()->getClassMetadata(ClassUtils::getClass($entity));

        // Récupération des propriétés de la classe
        $properties = $classMetadata->getReflectionProperties();

        // Pour chaque propriété de l'objet
        foreach ($properties as $property) {
            // Si on a une annotation de relation simple
            if ($manyToOne = $this->recordNormalizer->findManyToOneAssociation($property)) {
                // On rend accessible la propriété
                $property->setAccessible(true);

                // Récupération de la valeur  de la propriété
                $value = $property->getValue($entity);

                // Si pas de valeur
                if (null === $value) {
                    // Propriété suivante
                    continue;
                }

                // Si on a pas un entier
                if (!is_int($value)) {
                    // Propriété suivante
                    continue;
                }

                // Création d'une réflection de la classe associée
                $reflection = new ReflectionClass($manyToOne->class);

                // Création de l'enregistrement
                $record = $reflection->newInstanceWithoutConstructor();

                // Assignation de l'ID de l'enregistrement
                $record->setId($value);

                // On remplace la valeur de la propriété
                $property->setValue($entity, $record);

                // Propriété suivante
                continue;
            }
        }
    }
}
