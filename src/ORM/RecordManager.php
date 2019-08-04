<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use Exception;
use LogicException;
use ReflectionClass;
use ReflectionProperty;
use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\Annotations;
use Ang3\Bundle\OdooApiBundle\Exception\RecordNotFoundException;
use Ang3\Bundle\OdooApiBundle\Model\Record;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\ArrayTransformerInterface;

/**
 * @author Joanis ROUANET
 */
class RecordManager
{
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTranformer;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @var ModelRegistry
     */
    private $modelRegistry;

    /**
     * Original data of loaded records.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Constructor of the manager.
     *
     * @param ArrayTransformerInterface $arrayTranformer
     * @param Reader                    $reader
     * @param ExternalApiClient         $client
     * @param ModelRegistry             $modelRegistry
     */
    public function __construct(ArrayTransformerInterface $arrayTranformer, Reader $reader, ExternalApiClient $client, ModelRegistry $modelRegistry)
    {
        $this->arrayTranformer = $arrayTranformer;
        $this->reader = $reader;
        $this->client = $client;
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * Persist a record.
     *
     * @param RecordInterface $record
     *
     * @throws LogicException when a record cannot be created
     */
    public function persist(RecordInterface $record)
    {
        // Récupération du nom du modèle de l'enregistrement
        $model = $this->modelRegistry->resolve($record);

        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si l'enregistrement ne possède pas encore d'identifiant
        if (null === $id) {
            // Si l'enregistrement est un utilisateur
            if ('res.user' == $model) {
                throw new LogicException('Unable to create a user from API - To create new user please go to Odoo configuration panel!');
            }

            // Création de l'enregitrement et récupération de l'ID
            $id = $this->client->create($model, $this->normalize($record));

            // Récupération de l'enregistrement enregistré
            $record = $this->get($model, $id);

            // Enregistrement des données originelles
            $this->setOriginalData($record);
        } else {
            // Récupération des changements
            $changeSet = $this->getChangeSet($record);

            // Si pas de changement
            if (!$changeSet) {
                // Retour de l'enregistrement
                return $record;
            }

            // Mise-à-jour de l'enregistrement
            $this->client->update($model, $id, $changeSet);
        }
    }

    /**
     * Delete a record.
     *
     * @param Record $record
     *
     * @return self
     */
    public function delete(Record $record)
    {
        // Si pas d'ID
        if (!$record->getId()) {
            // Retour du manager
            return $this;
        }

        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($record);

        // Retour de la suppression par l'ID
        return $this->deleteById($model, $record->getId());
    }

    /**
     * Delete a record by model and ID.
     *
     * @param string    $class
     * @param array|int $ids
     *
     * @return self
     */
    public function deleteById(string $class, $ids)
    {
        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($class);

        // Lancement de la requête de suppression
        $this->client->delete($model, $ids);

        // Retour du manager
        return $this;
    }

    /**
     * Reload a record.
     *
     * @param RecordInterface $record
     *
     * @return RecordInterface|null
     */
    public function reload(RecordInterface $record)
    {
        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si pas d'identifiant
        if (null === $id) {
            // Retour de l'enregistrement
            return $record;
        }

        // Récupération de l'enregistrement
        $refreshed = $this->find(get_class($record), $id);

        // Si pas d'enregistrement
        if (null === $refreshed) {
            // Pas d'enregistrement rechargé
            return null;
        }

        // Enregistrement des données originelles
        $this->setOriginalData($refreshed);

        // Retour de l'enregistrement mis-à-jour
        return $refreshed;
    }

    /**
     * Get a record by class and ID.
     *
     * @param string $class
     * @param int    $id
     * @param array  $options
     *
     * @throws RecordNotFoundException when the record was not foud on Odoo
     *
     * @return RecordInterface
     */
    public function get(string $class, int $id, array $options = [])
    {
        // Récupération du modèle
        $record = $this->find($class, $id, $options);

        // Si pas de modèle trouvé
        if (null === $record) {
            throw new RecordNotFoundException($this->client, $class, $id);
        }

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Find a record by model and ID.
     *
     * @param string $class
     * @param int    $id
     * @param array  $options
     *
     * @return RecordInterface|null
     */
    public function find(string $class, int $id, array $options = [])
    {
        return $this->findOneBy($class, [
            ['id', '=', $id],
        ], $options);
    }

    /**
     * Find one record by record class, domains and options.
     *
     * @param string $class
     * @param array  $domains
     * @param array  $options
     *
     * @return RecordInterface|null
     */
    public function findOneBy(string $class, array $domains = [], array $options = [])
    {
        // Récupération des entrées
        $records = $this->findBy($class, $domains, array_merge($options, [
            'limit' => 1,
        ]));

        // Si pas d'enregistrement
        if (!$records) {
            // Retour nul
            return null;
        }

        // Retour du premier enregistrement
        return $records[0];
    }

    /**
     * Find records by record model, domains and options.
     *
     * @param string $class
     * @param array  $domains
     * @param array  $options
     *
     * @return RecordInterface[]
     */
    public function findBy(string $class, array $domains = [], array $options = [])
    {
        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($class);

        // Normalisation des domaines
        $domains = $this->normalizeDomains($class, $domains);

        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->getSerializedNames($class);

        // Si pas d'options de champs particuliers
        if (!isset($options['fields'])) {
            // On filtre selon les champs de la classe
            $options['fields'] = array_values($serializedNames);
        }

        // Récupération des entrées
        $result = $this->client->searchAndRead($model, $domains, $options);

        // Pour chaque ligne de données
        foreach ($result as $key => $data) {
            // Création de l'enregistrement
            $record = $this->denormalize($data, $class);

            // On définit l'enregistrement comme étant chargé
            $this->setLoaded($record, true);

            // Mise-à-jour des données originelles
            $this->setOriginalData($record, $data);

            // Dénormalization des données
            $result[$key] = $record;
        }

        // Retour des données
        return $result;
    }

    /**
     * Get change set of a record.
     *
     * @param RecordInterface $record
     *
     * @return array
     */
    public function getChangeSet(RecordInterface $record)
    {
        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($record);

        // Normalization de l'enregistrement reçu
        $normalized = $this->normalize($record);

        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si pas encore d'identifiant
        if (null === $id) {
            // Retour de l'enregistrement normalisé
            return $normalized;
        }

        // Récupération des données originelles
        $original = $this->getOriginalData($record);

        // Si l'enregistrement ne possède pas de données originelles
        if (!$original) {
            // Retour de l'enregistrement normalisé
            return $normalized;
        }

        // Retour de la différence entre les données originelles et l'enregistrement normalisé
        return $this->diff($normalized, $original);
    }

    /**
     * Denormalize a record from Odoo data.
     *
     * @param array  $data
     * @param string $class
     *
     * @throws Exception when the record class is not valid
     *
     * @return RecordInterface
     */
    public function denormalize(array $data, string $class)
    {
        

        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($class);

        // Dénormlization de l'enregistrement
        $record = $this->arrayTranformer->fromArray($data, $class);

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Normalize a record.
     *
     * @param RecordInterface $record
     *
     * @return array
     */
    public function normalize(RecordInterface $record)
    {
        return $this->arrayTranformer->toArray($record);
    }

    /**
     * Normalize domains criteria names by Odoo model names.
     *
     * @internal
     *
     * @param string $class
     * @param array  $domains
     *
     * @return array
     */
    private function normalizeDomains(string $class, array $domains = [])
    {
        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->getSerializedNames($class);

        // Pour chaque domaine
        foreach ($domains as $key => &$criteria) {
            // Si on a un tableau (donc un critère)
            if (is_array($criteria) && !empty($criteria[0])) {
                // Noramlisation du nom du champ cible du critère
                $criteria[0] = $this->normalizeFieldName($class, $criteria[0]);
            }
        }

        // Retour des domaines
        return $domains;
    }

    /**
     * Normalize a flattened field name.
     *
     * @internal
     *
     * @param string $class
     * @param string $fieldName
     *
     * @return string
     */
    private function normalizeFieldName(string $class, string $fieldName)
    {
        // Réflection de la classe
        $reflection = new ReflectionClass($class);

        // Récupération des champs par explosion selon les points
        $fields = explode('.', $fieldName);

        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->getSerializedNames($class);

        // Pour chaque champ à traverser
        foreach ($fields as $key => &$field) {
            // Récupération du nom de la propriété
            $propertyName = $field;

            // Mise-à-jour du nom du champ
            $field = array_key_exists($field, $serializedNames) ? $serializedNames[$field] : $field;

            // Si la classe possède la propriété
            if ($reflection->hasProperty($propertyName)) {
                // Récupération de la réflection de la propriété
                $property = $reflection->getProperty($propertyName);

                // Récupération éventuelle d'une annotation de relation simple
                $manyToOne = $this->findManyToOneAssociation($property);

                // Si pas d'annotation
                if (null === $manyToOne) {
                    // Champ suivant
                    continue;
                }

                // Changement de classe courante
                $reflection = new ReflectionClass($manyToOne->class);

                // Mise-à-jour des nom de champs sérialisés selon la nouvelle classe
                $serializedNames = $this->getSerializedNames($manyToOne->class);
            }
        }

        // Retour du champ aplati normalisé
        return implode('.', $fields);
    }

    /**
     * Get serialized names of class properties by property name.
     *
     * @internal
     *
     * @param string $class
     *
     * @return array
     */
    private function getSerializedNames(string $class)
    {
        // Réflection de la classe
        $reflection = new ReflectionClass($class);

        // Récupération des propriétés de la classe
        $properties = $reflection->getProperties();

        // Initialisation des noms de propriété sérialisés
        $serializedNames = [];

        // Pour chaque propriété
        foreach ($properties as $property) {
            /**
             * Récupération d'une annotation éventuelle du nom sérialisé de la propriété.
             *
             * @var JMS\SerializedName|null
             */
            $annotation = $this->reader->getPropertyAnnotation($property, JMS\SerializedName::class);

            // Si pas d'annotation
            if (null === $annotation) {
                // Enregistrement du nom sérialisé de la propriété par rapport à son nom
                $serializedNames[$property->getName()] = $property->getName();

                // Propriété suivante
                continue;
            }

            // Enregistrement du nom sérialisé de la propriété par rapport à son nom
            $serializedNames[$property->getName()] = $annotation->name;
        }

        // Retour des noms de propriété sérialisés
        return $serializedNames;
    }

    /**
     * Set data for a model.
     *
     * @internal
     *
     * @param RecordInterface $record
     * @param array           $data
     *
     * @return self
     */
    private function setOriginalData(RecordInterface $record, array $data = [])
    {
        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si pas d'identifiant
        if (null === $id) {
            // Retour du manager
            return $this;
        }

        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($record);

        // Si le modèle n'est pas encore déclaré
        if (array_key_exists($model, self::$cache)) {
            // Initialisation des entrées du modèle'
            self::$cache[$model] = [];
        }

        // Enregistrement des données identifiées
        self::$cache[$model][$id] = $data;

        // Retour du cache
        return $this;
    }

    /**
     * Get cached data of a model.
     *
     * @param RecordInterface $record
     *
     * @return array
     */
    public function getOriginalData(RecordInterface $record)
    {
        // Récupération de l'identifiant de l'enregistrement
        $id = $record->getId();

        // Si pas d'identifiant
        if (null === $id) {
            // Retour d'un tableau vide
            return [];
        }

        // Récupéraion du nom du modèle
        $model = $this->modelRegistry->resolve($record);

        // Si on a des données pour ce mocèle identifié
        if (!empty(self::$cache[$model][$id])) {
            // Retour des données
            return self::$cache[$model][$id];
        }

        // Pas de données
        return [];
    }

    /**
     * Record setter on '__loaded' property.
     *
     * @param RecordInterface $record
     * @param bool|bool       $isLoaded
     */
    public function setLoaded(RecordInterface $record, bool $isLoaded = true)
    {
        // Réflection de la propriété cible
        $property = new ReflectionProperty(get_class($record), '__loaded');

        // On rend accessible la propriété privée
        $property->setAccessible(true);

        // On assigne la valeur à la propriété
        $property->setValue($record, $isLoaded);
    }

    /**
     * Find ManyToOne association on property.
     * 
     * @param  ReflectionProperty $property
     * 
     * @return Annotations\ManyToOne|null
     */
    public function findManyToOneAssociation(ReflectionProperty $property)
    {
        /** @var Annotations\ManyToOne|null */
        $manyToOne = $this->reader->getPropertyAnnotation($property, Annotations\ManyToOne::class);

        // Retour de l'association éventuelle
        return $manyToOne;
    }

    /**
     * @see https://www.php.net/manual/fr/function.array-diff-assoc.php#111675
     *
     * @internal
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function diff($array1, $array2)
    {
        // Initialisation de la différence
        $diff = array();

        // Pour chaque valeur du tableau 1
        foreach ($array1 as $key => $value) {
            // Si la valeur est un tableau
            if (is_array($value)) {
                // Si la clé n'existe pas ou n'est pas un tableau dans le tableau 2
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    // Enregistrement de la valeur de cette clé
                    $diff[$key] = $value;
                } else {
                    // Calcul de la différece de cette valeur par récursion
                    $new_diff = $this->diff($value, $array2[$key]);

                    // Si on a une différence
                    if (!empty($new_diff)) {
                        // Enregistrement de cette différence sur la clé
                        $diff[$key] = $new_diff;
                    }
                }
                // Sinon si la clé n'existe pas dans le tableau 2 ou que la valeur est différente
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                // Enregistrement de la valeur de cette clé
                $diff[$key] = $value;
            }
        }

        // Retour de la différence
        return $diff;
    }
}
