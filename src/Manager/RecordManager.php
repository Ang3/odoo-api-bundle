<?php

namespace Ang3\Bundle\OdooApiBundle\Manager;

use Exception;
use LogicException;
use ReflectionClass;
use Ang3\Component\OdooApiClient\Client\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\Annotations;
use Ang3\Bundle\OdooApiBundle\Exception\RecordNotFoundException;
use Ang3\Bundle\OdooApiBundle\Model\Record;
use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\Model\Res\User;
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
     * Original data of loaded records.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Class map of Odoo models.
     *
     * @var array
     */
    private static $models = [];

    /**
     * Constructor of the manager.
     *
     * @param ExternalApiClient $client
     */
    public function __construct(ExternalApiClient $client)
    {
        $this->client = $client;
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
        $model = $this->getModelName($record);

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
        $model = $this->getModelName($record);

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
        $model = $this->getModelName($class);

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
        // Récupéraion du nom du modèle
        $model = $this->getModelName($record);

        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si pas d'identifiant
        if (null === $id) {
            // Retour de l'enregistrement
            return $record;
        }

        // Récupération de l'enregistrement
        $refreshed = $this->find($model, $id);

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
     * Get a record by model and ID.
     *
     * @param string $model
     * @param int    $id
     * @param array  $options
     *
     * @throws RecordNotFoundException when the record was not foud on Odoo
     *
     * @return RecordInterface
     */
    public function get(string $model, int $id, array $options = [])
    {
        // Récupération du modèle
        $record = $this->find($model, $id, $options);

        // Si pas de modèle trouvé
        if (null === $record) {
            throw new RecordNotFoundException($this->client, $model, $id);
        }

        // Mise-à-jour des données originelles
        $this->setOriginalData($record);

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Load a record from many to one association.
     *
     * @param ManyToOne $manyToOne
     *
     * @throws Exception when the manyToOne hasn't specified target class
     *
     * @return RecordInterface|null
     */
    public function load(ManyToOne $manyToOne)
    {
        // Récupération de la classe cible
        $target = $manyToOne->getTarget();

        // Si pas de classe cible
        if (null === $target) {
            throw new Exception(sprintf('Unable to get target class of property "%s::$%s" (id: %s) - Did you forget to implement "ManyToOne" annotation on property?', $manyToOne->getClass(), $manyToOne->getProperty(), $manyToOne->getId() ?: 'null'));
        }

        // Si pas d'identifiant
        if (null === $manyToOne->getId()) {
            // Pas d'enregistrement
            return null;
        }

        // Retour de la recherche de l'entité
        return $this->find($target, $manyToOne->getId());
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
        // Récupéraion du nom du modèle
        $model = $this->getModelName($class);

        // Normalisation des domaines
        $domains = $this->normalizeDomains($class, $domains);

        // Récupération des entrées
        $records = $this->client->searchAndRead($model, $domains, array_merge($options, [
            'limit' => 1,
        ]));

        // Retour de la dénormlisation de la lecture sur l'ID
        return $records ? $this->denormalize($class, $records[0]) : null;
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
        $model = $this->getModelName($class);

        // Récupération des entrées
        $result = $this->client->searchAndRead($model, $domains, $options);

        // Pour chaque ligne de données
        foreach ($result as $key => $data) {
            // Création de l'enregistrement
            $record = $this->denormalize($class, $data);

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
        $model = $this->getModelName($record);

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
        $original = $this->getOriginalData($model, $id);

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
     * @param string $class
     * @param array  $data
     *
     * @throws Exception when the record class is not valid
     *
     * @return RecordInterface
     */
    public function denormalize(string $class, array $data)
    {
        // Récupéraion du nom du modèle
        $model = $this->getModelName($class);

        // Dénormlization de l'enregistrement
        $record = $this->arrayTranformer->fromArray($data, $class);

        // Mise-à-jour des données originelles
        $this->setOriginalData($record);

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

                /**
                 * On recherche une annotation ManyToOne sur la propriété.
                 *
                 * @var Annotations\ManyToOne|null
                 */
                $manyToOne = $this->reader->getPropertyAnnotation($property, Annotations\ManyToOne::class);

                // Si pas d'annotation
                if (null === $manyToOne) {
                    // Champ suivant
                    break;
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
     *
     * @return self
     */
    private function setOriginalData(RecordInterface $record)
    {
        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si pas d'identifiant
        if (null === $id) {
            // Retour du manager
            return $this;
        }

        // Récupéraion du nom du modèle
        $model = $this->getModelName($record);

        // Si le modèle n'est pas encore déclaré
        if (array_key_exists($model, self::$cache)) {
            // Initialisation des entrées du modèle'
            self::$cache[$model] = [];
        }

        // Enregistrement des données identifiées
        self::$cache[$model][$id] = $this->normalize($record);

        // Retour du cache
        return $this;
    }

    /**
     * Get cached data of a model.
     *
     * @internal
     *
     * @param string $model
     * @param int    $id
     *
     * @return array
     */
    private function getOriginalData(string $model, int $id)
    {
        // Si on a des données pour ce mocèle identifié
        if (!empty(self::$cache[$model][$id])) {
            // Retour des données
            return self::$cache[$model][$id];
        }

        // Pas de données
        return [];
    }

    /**
     * Get the name of the record class from "Model" annotation.
     *
     * @internal
     *
     * @param object|scalar $objectOrClass
     *
     * @throws Exception when the class does not implement interface RecordInterface or does not implement annotation "Model"
     *
     * @return string
     */
    private function getModelName($objectOrClass)
    {
        // Définition de la classe à vérifier
        $class = is_object($objectOrClass) ? get_class($objectOrClass) : (string) $objectOrClass;

        // Si on a déjà le nom du modèle pour cette classe
        if (!empty(self::$models[$class])) {
            // Retour du nom du modèle stocké en cache
            return self::$models[$class];
        }

        // Si la classe n'implémente pas l'interface d'enregistrement de Odoo
        if (!in_array(RecordInterface::class, class_implements($class))) {
            throw new Exception(sprintf('The class "%s" does not represent a record. Did you forget to implement interface "%s"?', $class, RecordInterface::class));
        }

        // Si pas d'annotation de modèle
        if (!($annotation = $this->getModelAnnotation($class))) {
            throw new Exception(sprintf('The class "%s" does not represent a model. Did you forget to implement annotation "%s"?', $class, Annotations\Model::class));
        }

        // Enregistrement du nom du modèle pour cette classe
        self::$models[$class] = $annotation->name;

        // Retour du nom du modèle
        return $annotation->name;
    }

    /**
     * Get the "Model" annotation of a class.
     *
     * @internal
     *
     * @param string $class
     *
     * @throws Exception when the class was not found
     *
     * @return Annotations\Model|null
     */
    private function getModelAnnotation(string $class)
    {
        // Si la classe du modèle n'existe pas
        if (!class_exists($class)) {
            throw new Exception(sprintf('Model class "%s" not found.', $class));
        }

        // Réflection du modèle
        $reflection = new ReflectionClass($class);

        // Recherche de l'annotation "Model"
        $annotation = $this->reader->getClassAnnotation($reflection, Annotations\Model::class);

        // Retur de l'annotation
        return $annotation instanceof Annotations\Model ? $annotation : null;
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

    /**
     * @return ExternalApiClient
     */
    public function getClient()
    {
        return $this->client;
    }
}
