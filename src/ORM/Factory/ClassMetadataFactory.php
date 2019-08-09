<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Factory;

use ReflectionClass;
use ReflectionProperty;
use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Cache\ClassMetadataCache;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\FieldMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\SingleAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\MultipleAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\MultipleAssociation;
use Doctrine\Common\Annotations\Reader;

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
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
        $this->classMetadataCache = new ClassMetadataCache;
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
        // Récupération des métadonnées depuis le cache mémoire
        $classMetadata = $this->classMetadataCache->get($class);

        // Si pas de métadonnées
        if (null === $classMetadata) {
            // Chargement effectif des métadonnées
            $classMetadata = $this->doLoad($class);
        }

        // Enregistrement des métadonnées dans le cache mémoire
        $this->classMetadataCache->set($class, $classMetadata);

        // Retour des métadonnées
        return $classMetadata;
    }

    /**
     * Create (or load cached) metadata of a class.
     *
     * @internal
     *
     * @param string             $class
     * @param ClassMetadata|null $classMetadata
     *
     * @throws MappingException when the class is not valid
     *
     * @return ClassMetadata
     */
    private function doLoad(string $class, ClassMetadata $classMetadata = null)
    {
        // Récupération de la réflection de la classe
        $reflection = new ReflectionClass($class);

        // Si la classe n'implémente pas l'interface d'un enregistrement
        if (!$reflection->implementsInterface(RecordInterface::class)) {
            throw new MappingException(sprintf('The class "%s" does not implement record interface "%s"', $class, RecordInterface::class));
        }

        /** @var ORM\Model|null */
        $model = $this->reader->getClassAnnotation($class, ORM\Model::class);

        // Si pas de modèle
        if (null === $model) {
            throw new MappingException(sprintf('Missing annotation "%s" on class "%s"', ORM\Model::class, $class));
        }

        // Définition de l'instance des métadonnées cibles
        $classMetadata = $classMetadata ?: new ClassMetadata($class, $model->name);

        // Pour chaque propriété de la classe
        foreach ($reflection->getProperties() as $property) {
            // Si on est sur une autre classe que celle dont on veut charger les métadonnées
            if($class !== $classMetadata->getClass()) {
                // Si la propriété est privée
                if($property->isPrivate()) {
                    // Propriété suivante
                    continue;
                }

                // Si la propriété est déjà mappée
                if($classMetadata->hasProperty($property->getName())) {
                    // Propriété suivante
                    continue;
                }
            }

            /** @var ORM\Field|null */
            $field = $this->reader->getPropertyAnnotation($property, ORM\Field::class);

            // Si on a un champ à mapper
            if(null !== $field) {
                // Définition du nom du champ distant
                $remoteName = $field->name ?: $property->getName();

                // Enregistrement du champ
                $classMetadata->addProperty(new FieldMetadata($property->getName(), $remoteName, $field->type, $field->nullable));
            }

            /** @var ORM\ManyToOne|null */
            $manyToOne = $this->reader->getPropertyAnnotation($property, ORM\ManyToOne::class);

            // Si on a une relation ManyToOne
            if (null !== $manyToOne) {
                // Définition du nom du champ distant
                $remoteName = $manyToOne->name ?: $property->getName();

                // Enregistrement de l'association
                $classMetadata->addProperty(new ManyToOneMetadata($property->getName(), $remoteName, $manyToOne->class, $manyToOne->nullable));
            }

            /** @var ORM\ManyToMany|null */
            $manyToMany = $this->reader->getPropertyAnnotation($property, ORM\ManyToMany::class);

            // Si on a une relation ManyToMany
            if (null !== $manyToMany) {
                // Définition du nom du champ distant
                $remoteName = $manyToMany->name ?: $property->getName();

                // Enregistrement de l'association
                $classMetadata->addProperty(new ManyToManyMetadata($property->getName(), $remoteName, $manyToMany->class, $manyToMany->nullable));
            }

            /** @var ORM\OneToMany|null */
            $oneToMany = $this->reader->getPropertyAnnotation($property, ORM\OneToMany::class);

            // Si on a une relation OneToMany
            if (null !== $oneToMany) {
                // Définition du nom du champ distant
                $remoteName = $oneToMany->name ?: $property->getName();

                // Enregistrement de l'association
                $classMetadata->addProperty(new OneToManyMetadata($property->getName(), $remoteName, $oneToMany->class, $oneToMany->nullable));
            }
        }

        // Récupération de la classe parente éventuelle
        $parentClass = $reflection->getParentClass();

        // Si on a une classe parente
        if(null !== $parentClass) {
            // Retour du chargement récursif de la/les classe(s) parente(s)
            return $this->doLoad($parentClass->getClass(), $classMetadata);
        }

        // Retour des métadonnées
        return $classMetadata;
    }
}
