<?php

namespace Ang3\Bundle\OdooApiBundle\Doctrine\ORM\EventListener;

use ReflectionClass;
use Ang3\Component\Odoo\ORM\Normalizer;
use Ang3\Component\Odoo\ORM\Factory\ClassMetadataFactory;
use Ang3\Component\Odoo\ORM\Mapping\ManyToOneMetadata;
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
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @param ClassMetadataFactory $classMetadataFactory
     * @param Normalizer           $normalizer
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory, Normalizer $normalizer)
    {
        $this->classMetadataFactory = $classMetadataFactory;
        $this->normalizer = $normalizer;
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

        // Relevé de la classe de l'entité
        $entityClass = ClassUtils::getClass($entity);

        // Récupération des propriétés mappés de la classe
        $properties = $eventArgs
            ->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getReflectionProperties()
        ;

        // Récupération des métadonnées de la classe
        $classMetadata = $this->classMetadataFactory->load($entityClass);

        // Pour chaque propriété de l'objet
        foreach ($classMetadata->iterateAssociations() as $property) {
            // Si c'est une association simple
            if ($property instanceof ManyToOneMetadata) {
                // Récupération de la valeur de la propriété
                $value = $classMetadata->getValue($entity, $property);

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
                $reflection = new ReflectionClass($property->getTargetClass());

                // Création de l'enregistrement
                $record = $reflection->newInstanceWithoutConstructor();

                // Assignation de l'ID de l'enregistrement
                $classMetadata
                    ->setValue($record, $this->classMetadataFactory->load($property->getTargetClass())->getProperty('id'), $value)
                    ->setValue($entity, $property, $record)
                ;

                // Propriété suivante
                continue;
            }
        }
    }
}
