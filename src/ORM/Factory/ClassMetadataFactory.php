<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Factory;

use ReflectionClass;
use ReflectionProperty;
use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\FieldMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\SingleAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\MultipleAssociationMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\MultipleAssociation;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation as JMS;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
     * @var CacheInterface
     */
    private $systemCache;

    /**
     * @param Reader         $reader
     * @param CacheInterface $systemCache
     */
    public function __construct(Reader $reader, CacheInterface $systemCache)
    {
        $this->reader = $reader;
        $this->systemCache = $systemCache;
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
        // Récupération du lecteur d'annotations
        $callback = [$this, 'doLoad'];

        // Retour des métadonnées stockées en cache
        return unserialize($this->systemCache->get($this->generateCacheKey($class), function (ItemInterface $item) use ($class, $callback) {
            // Retour de la désérialisation des modèles
            return serialize($callback($class));
        }));
    }

    /**
     * Create (or load cached) metadata of a class.
     *
     * @internal
     *
     * @param string $class
     *
     * @throws MappingException when the class is not valid
     *
     * @return ClassMetadata
     */
    private function doLoad(string $class)
    {
        // Retour de la construction de l'instance des mtadonnées
        $classMetadata = new ClassMetadata($class);

        // Si la classe n'implémente pas l'interface d'un enregistrement
        if (!$classMetadata->implementsInterface(RecordInterface::class)) {
            throw new MappingException(sprintf('The class "%s" does not implement record interface "%s"', $class, RecordInterface::class));
        }

        // Recherche du de l'annotation du modèle
        $model = $this->findModelAssociation($classMetadata);

        // Si pas de modèle
        if (null === $model) {
            throw new MappingException(sprintf('Missing annotation "%s" on class "%s"', ORM\Model::class, $class));
        }

        // Hydratation du modèle de la classe
        $classMetadata->setModel($model->name);

        // Pour chaque propriété de la classe
        foreach ($classMetadata->getProperties() as $property) {
            /**
             * Récupération d'une annotation d'exclusion éventuelle.
             *
             * @var JMS\Exclude|null
             */
            $excluded = $this->reader->getPropertyAnnotation($property, JMS\Exclude::class);

            /**
             * Récupération du nom sérialisé éventuel.
             *
             * @var JMS\SerializedName|null
             */
            $serializedName = $this->reader->getPropertyAnnotation($property, JMS\SerializedName::class);

            // Définition du nom sérialisé selon la présence d'annotation ou non
            $serializedName = null !== $serializedName ? $serializedName->name : $property->getName();

            // Si on a une annotation d'exclusion
            if (null !== $excluded) {
                // Propriété suivante
                continue;
            }

            // Si on a une association simple
            if ($association = $this->findSingleAssociation($property)) {
                // Enregistrement du nom sérialisé de la propriété
                $association->setSerializedName($serializedName);

                // Enregistrement de la propriété
                $classMetadata->addProperty($association);

                // Propriété suivante
                continue;
            }

            // Si on a une association mutiple
            if ($association = $this->findMultipleAssociation($property)) {
                // Enregistrement du nom sérialisé de la propriété
                $association->setSerializedName($serializedName);

                // Enregistrement de la propriété
                $classMetadata->addProperty($association);

                // Propriété suivante
                continue;
            }

            // Enregistrement de l'association
            $property = new FieldMetadata($classMetadata->getName(), $property->getName());

            // Enregistrement du nom sérialisé de la propriété
            $property->setSerializedName($serializedName);

            // Enregistrement de la propriété
            $classMetadata->addProperty($property);
        }

        // Retour des métadonnées
        return $classMetadata;
    }

    /**
     * Find single association on property.
     *
     * @param ReflectionProperty $property
     *
     * @return SingleAssociationMetadata|null
     */
    public function findSingleAssociation(ReflectionProperty $property)
    {
        // Recherche d'une classe cible pour une association simple
        $targetClass = $this->findAssociation($property, SingleAssociation::class);

        // Si pas de classe cible
        if (null === $targetClass) {
            // Retour null;
            return null;
        }

        // Retour de l'association simple
        return new SingleAssociationMetadata($property->getDeclaringClass()->getName(), $property->getName(), $targetClass);
    }

    /**
     * Find multiple association on property.
     *
     * @param ReflectionProperty $property
     *
     * @return MultipleAssociationMetadata|null
     */
    public function findMultipleAssociation(ReflectionProperty $property)
    {
        // Recherche d'une classe cible pour une association simple
        $targetClass = $this->findAssociation($property, MultipleAssociation::class);

        // Si pas de classe cible
        if (null === $targetClass) {
            // Retour null;
            return null;
        }

        // Retour de l'association simple
        return new MultipleAssociationMetadata($property->getDeclaringClass()->getName(), $property->getName(), $targetClass);
    }

    /**
     * Find association on property.
     *
     * @param ReflectionProperty $property
     * @param string             $class
     *
     * @throws MappingException when the association target class found but not valid
     *
     * @return string|null
     */
    private function findAssociation(ReflectionProperty $property, string $class)
    {
        /** @var JMS\Type|null */
        $annotation = $this->reader->getPropertyAnnotation($property, JMS\Type::class);

        // Si pas d'annotation
        if (null === $annotation) {
            // Retour nul
            return null;
        }

        // Relevé de la position éventuelle de la classe dans le nom du type complet
        $position = strpos($annotation->name, $class);

        // Si la classe n'est pas présente en début de nom du type
        if (0 !== $position) {
            // Retour nul
            return null;
        }

        // Définition de la classe cible
        $targetClass = substr($annotation->name, strlen($annotation->name));
        $targetClass = str_replace('<', null, $targetClass);
        $targetClass = str_replace('>', null, $targetClass);
        $targetClass = str_replace('\'', null, $targetClass);
        $targetClass = str_replace('"', null, $targetClass);

        // Si la classe cible n'existe pas
        if (!class_exists($targetClass)) {
            throw new MappingException(sprintf('The target class "%s" of association on property "%s::%s" does not exist', $targetClass, $property->getDeclaringClass(), $property->getName()));
        }

        // Réflection de la classe cible
        $reflection = new ReflectionClass($targetClass);

        // Si la classe n'implémente pas l'interface d'un enregistrement
        if (!$reflection->implementsInterface(RecordInterface::class)) {
            throw new MappingException(sprintf('The target class "%s" of association on property "%s::%s" does not implement record interface "%s"', $targetClass, $property->getDeclaringClass(), $property->getName(), RecordInterface::class));
        }

        // Retour de la classe cible
        return $targetClass;
    }

    /**
     * Find a model annotation.
     *
     * @param ReflectionClass $class
     *
     * @return ORM\Model|null
     */
    public function findModelAssociation(ReflectionClass $class)
    {
        /** @var ORM\Model|null */
        $annotation = $this->reader->getClassAnnotation($class, ORM\Model::class);

        // Retour de l'association éventuelle
        return $annotation;
    }

    /**
     * Generate a key for cache item.
     *
     * @internal
     *
     * @param string $class
     *
     * @return string
     */
    private function generateCacheKey(string $class)
    {
        return sprintf('ang3_odoo_api.metadata.%s', str_replace('\\', '_', $class));
    }
}
