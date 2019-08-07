<?php

namespace Ang3\Bundle\OdooApiBundle\Doctrine\ORM\EventListener;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\RecordNormalizer;
use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Joanis ROUANET
 */
class DoctrineListener
{
    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var RecordNormalizer
     */
    private $recordNormalizer;

    /**
     * @param ClassMetadataFactory $classMetadataFactory
     * @param RecordNormalizer     $recordNormalizer
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory, RecordNormalizer $recordNormalizer)
    {
        $this->classMetadataFactory = $classMetadataFactory;
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
        $classMetadata = $eventArgs
            ->getEntityManager()
            ->getClassMetadata(ClassUtils::getClass($entity))
        ;

        // Pour chaque propriété de l'objet
        foreach ($classMetadata->getReflectionProperties() as $property) {
            // Si on a une annotation de relation simple
            if ($association = $this->classMetadataFactory->findSingleAssociation($property)) {
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
                $reflection = new ReflectionClass($association->class);

                // Création de l'enregistrement
                $record = $reflection->newInstanceWithoutConstructor();

                // Assignation de l'ID de l'enregistrement
                $this->recordNormalizer->setRecordId($record, $value);

                // On remplace la valeur de la propriété
                $property->setValue($entity, $record);

                // Propriété suivante
                continue;
            }
        }
    }
}
