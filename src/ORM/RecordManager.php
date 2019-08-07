<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use LogicException;
use ReflectionProperty;
use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\RecordNotFoundException;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;

/**
 * @author Joanis ROUANET
 */
class RecordManager
{
    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @var Catalog
     */
    private $catalog;

    /**
     * @var RecordNormalizer
     */
    private $normalizer;

    /**
     * Original data of loaded records.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Constructor of the manager.
     *
     * @param ExternalApiClient $client
     * @param Catalog     $catalog
     * @param RecordNormalizer  $normalizer
     */
    public function __construct(ExternalApiClient $client, Catalog $catalog, RecordNormalizer $normalizer)
    {
        $this->client = $client;
        $this->catalog = $catalog;
        $this->normalizer = $normalizer;
    }

    /**
     * Persist a record.
     *
     * @param Record $record
     *
     * @throws LogicException when a record cannot be created
     */
    public function persist(RecordInterface $record)
    {
        // Récupération du nom du modèle de l'enregistrement
        $model = $this->catalog->resolve($record);

        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si l'enregistrement ne possède pas encore d'identifiant
        if (null === $id) {
            // Si l'enregistrement est un utilisateur
            if ('res.user' == $model) {
                throw new LogicException('Unable to create a user from API - To create new user please go to Odoo configuration panel!');
            }

            // Création de l'enregitrement et récupération de l'ID
            $id = $this->client->create($model, $data = $this->normalizer->normalize($record));

            // Enregistrement de l'ID de l'enregistrement
            $record->setId($id);

            // Enregistrement des données originelles
            $this->setOriginalData($record, $data);
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
        $model = $this->catalog->resolve($record);

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
        $model = $this->catalog->resolve($class);

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
        $model = $this->catalog->resolve($class);

        // Normalisation des domaines
        $domains = $this->normalizer->normalizeDomains($class, $domains);

        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->normalizer->getSerializedNames($class);

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
            $record = $this->normalizer->denormalize($data, $class);

            // Mise-à-jour des données originelles
            $this->setOriginalData($record);

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
        $model = $this->catalog->resolve($record);

        // Normalization de l'enregistrement reçu
        $normalized = $this->normalizer->normalize($record);

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

        // On renseigne l'enregistrement comme étant chargé
        $this->setLoaded($record, true);

        // Récupéraion du nom du modèle
        $model = $this->catalog->resolve($record);

        // Si le modèle n'est pas encore déclaré
        if (array_key_exists($model, self::$cache)) {
            // Initialisation des entrées du modèle'
            self::$cache[$model] = [];
        }

        // Enregistrement des données identifiées
        self::$cache[$model][$id] = $data ?: $this->normalizer->normalize($record);

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
        $model = $this->catalog->resolve($record);

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
     * @internal
     *
     * @param RecordInterface $record
     * @param bool|bool       $isLoaded
     */
    private function setLoaded(RecordInterface $record, bool $isLoaded = true)
    {
        // Réflection de la propriété cible
        $property = new ReflectionProperty(get_class($record), '__loaded');

        // On rend accessible la propriété privée
        $property->setAccessible(true);

        // On assigne la valeur à la propriété
        $property->setValue($record, $isLoaded);
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

    /**
     * @return Catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * @return RecordNormalizer
     */
    public function getNormalizer()
    {
        return $this->normalizer;
    }
}
