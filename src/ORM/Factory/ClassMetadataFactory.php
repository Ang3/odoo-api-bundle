<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Factory;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Cache\ClassMetadataCache;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\FieldMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToManyMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ManyToOneMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\OneToManyMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\TypeCollection;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;

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
     * @var TypeCollection
     */
    private $types;

    /**
     * @var ClassMetadataCache
     */
    private $classMetadataCache;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader, TypeCollection $types)
    {
        $this->reader = $reader;
        $this->types = $types;
        $this->classMetadataCache = new ClassMetadataCache();
    }

    /**
     * Load metadata of a class.
     *
     * @param object|string $objectOrClass
     *
     * @return ClassMetadata
     */
    public function load($objectOrClass)
    {
        // Définition de la classe cible
        $class = is_object($objectOrClass) ? ClassUtils::getClass($objectOrClass) : $objectOrClass;

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

        // Si on a pas encore de métadonnées cible
        if (null === $classMetadata) {
            /** @var ORM\Model|null */
            $model = $this->reader->getClassAnnotation($reflection, ORM\Model::class);

            // Si pas de modèle
            if (null === $model) {
                throw new MappingException(sprintf('Missing annotation "%s" on class "%s"', ORM\Model::class, $class));
            }

            // Définition de l'instance des métadonnées cibles
            $classMetadata = new ClassMetadata($class, $model->name);
        }

        // Pour chaque propriété de la classe
        foreach ($reflection->getProperties() as $property) {
            // Si on est sur une autre classe que celle dont on veut charger les métadonnées
            if ($class !== $classMetadata->getClass()) {
                // Si la propriété est privée
                if ($property->isPrivate()) {
                    // Propriété suivante
                    continue;
                }

                // Si la propriété est déjà mappée
                if ($classMetadata->hasProperty($property->getName())) {
                    // Propriété suivante
                    continue;
                }
            }

            /** @var ORM\ReadOnly|null */
            $readOnly = $this->reader->getPropertyAnnotation($property, ORM\ReadOnly::class);

            // Initialisation des options par défaut
            $options = [
                'read_only' => null !== $readOnly ? true : false,
                'nullable' => true,
            ];

            /** @var ORM\Field|null */
            $field = $this->reader->getPropertyAnnotation($property, ORM\Field::class);

            // Si on a un champ à mapper
            if (null !== $field) {
                // Définition du nom du champ distant
                $remoteName = $field->name ?: $property->getName();

                // Complétion des options
                $options['nullable'] = $field->nullable;

                // Enregistrement du champ
                $classMetadata->addProperty(new FieldMetadata($property->getName(), $remoteName, $this->types->get($field->type), $options));
            }

            /** @var ORM\ManyToOne|null */
            $manyToOne = $this->reader->getPropertyAnnotation($property, ORM\ManyToOne::class);

            // Si on a une relation ManyToOne
            if (null !== $manyToOne) {
                // Définition du nom du champ distant
                $remoteName = $manyToOne->name ?: $property->getName();

                // Complétion des options
                $options['nullable'] = $manyToOne->nullable;

                // Enregistrement de l'association
                $classMetadata->addProperty(new ManyToOneMetadata($property->getName(), $remoteName, $manyToOne->class, $options));
            }

            /** @var ORM\ManyToMany|null */
            $manyToMany = $this->reader->getPropertyAnnotation($property, ORM\ManyToMany::class);

            // Si on a une relation ManyToMany
            if (null !== $manyToMany) {
                // Définition du nom du champ distant
                $remoteName = $manyToMany->name ?: $property->getName();

                // Enregistrement de l'association
                $classMetadata->addProperty(new ManyToManyMetadata($property->getName(), $remoteName, $manyToMany->class, $options));
            }

            /** @var ORM\OneToMany|null */
            $oneToMany = $this->reader->getPropertyAnnotation($property, ORM\OneToMany::class);

            // Si on a une relation OneToMany
            if (null !== $oneToMany) {
                // Définition du nom du champ distant
                $remoteName = $oneToMany->name ?: $property->getName();

                // Enregistrement de l'association
                $classMetadata->addProperty(new OneToManyMetadata($property->getName(), $remoteName, $oneToMany->class, $options));
            }
        }

        // Récupération de la classe parente éventuelle
        $parentClass = $reflection->getParentClass();

        // Si on a une classe parente
        if ($parentClass instanceof ReflectionClass) {
            // Retour du chargement récursif de la/les classe(s) parente(s)
            return $this->doLoad($parentClass->getName(), $classMetadata);
        }

        // Retour des métadonnées
        return $classMetadata;
    }
}
