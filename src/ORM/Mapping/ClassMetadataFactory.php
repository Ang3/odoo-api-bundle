<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use ReflectionProperty;
use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class ClassMetadataFactory
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $loadedMetadata;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Load metadata of a class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function load(string $class)
    {
        // Si on a déjà chargé ces métadonnées
        if (array_key_exists($class, $this->loadedMetadata)) {
            // Retour des métadonnées précédemment chargés
            return $this->loadedMetadata[$class];
        }

        // Retour de la construction de l'instance des mtadonnées
        $classMetadata = new ClassMetadata($class);

        // Pour chaque propriété de la classe
        foreach ($classMetadata->iterateProperties() as $property) {
            /**
             * Récupération d'une annotation d'exclusion éventuelle.
             *
             * @var JMS\Exclude|null
             */
            $excluded = $this->reader->getPropertyAnnotation($property, JMS\Exclude::class);

            // Si on a une annotation d'exclusion
            if (null !== $excluded) {
                // Propriété suivante
                continue;
            }

            /**
             * Récupération du nom sérialisé éventuel.
             *
             * @var JMS\SerializedName|null
             */
            $serializedName = $this->reader->getPropertyAnnotation($property, JMS\SerializedName::class);

            // Définition du nom sérialisé selon la présence d'annotation ou non
            $serializedName = null !== $serializedName ? $serializedName->name : $property->getName();

            // Si on a une association de type ManyToOne
            if ($manyToOne = $this->findManyToOneAssociation($property)) {
                // Enregistrement de l'association
                $classMetadata->addProperty(new ManyToOneAssociationMetadata($property->getName(), $serializedName, $property, $manyToOne->class));

                // Propriété suivante
                continue;
            }

            // Si on a une association de type ManyToMany
            if ($manyToMany = $this->findManyToManyAssociation($property)) {
                // Enregistrement de l'association
                $classMetadata->addProperty(new ManyToManyAssociationMetadata($property->getName(), $serializedName, $property, $manyToMany->class));

                // Propriété suivante
                continue;
            }

            // Si on a une association de type OneToMany
            if ($oneToMany = $this->findOneToManyAssociation($property)) {
                // Enregistrement de l'association
                $classMetadata->addProperty(new OneToManyAssociationMetadata($property->getName(), $serializedName, $property, $oneToMany->class));

                // Propriété suivante
                continue;
            }

            // Enregistrement d'un champ simple'
            $classMetadata->addProperty(new FieldMetadata($property->getName(), $serializedName, $property));
        }

        // Enregistrement des métadonnées de la classe
        $this->loadedMetadata[$class] = $classMetadata;

        // Retour des métadonnées
        return $classMetadata;
    }

    /**
     * Find ManyToOne association on property.
     *
     * @param ReflectionProperty $property
     *
     * @return ORM\ManyToOne|null
     */
    public function findManyToOneAssociation(ReflectionProperty $property)
    {
        /** @var ORM\ManyToOne|null */
        $manyToOne = $this->reader->getPropertyAnnotation($property, ORM\ManyToOne::class);

        // Retour de l'association éventuelle
        return $manyToOne;
    }

    /**
     * Find ManyToMany association on property.
     *
     * @param ReflectionProperty $property
     *
     * @return ORM\ManyToMany|null
     */
    public function findManyToManyAssociation(ReflectionProperty $property)
    {
        /** @var ORM\ManyToMany|null */
        $manyToOne = $this->reader->getPropertyAnnotation($property, ORM\ManyToMany::class);

        // Retour de l'association éventuelle
        return $manyToOne;
    }

    /**
     * Find OneToMany association on property.
     *
     * @param ReflectionProperty $property
     *
     * @return ORM\OneToMany|null
     */
    public function findOneToManyAssociation(ReflectionProperty $property)
    {
        /** @var ORM\OneToMany|null */
        $manyToOne = $this->reader->getPropertyAnnotation($property, ORM\OneToMany::class);

        // Retour de l'association éventuelle
        return $manyToOne;
    }
}
