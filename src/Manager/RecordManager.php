<?php

namespace Ang3\Bundle\OdooApiBundle\Manager;

use LogicException;
use ReflectionProperty;
use Ang3\Component\OdooApiClient\Client\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\Exception\RecordNotFoundException;
use Ang3\Bundle\OdooApiBundle\Model\Record;

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
     * Original data of loaded records.
     *
     * @var array
     */
    private static $cache;

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
     * Create a new record.
     *
     * @param string $model
     * @param array  $data
     *
     * @throws LogicException when a record cannot be created
     *
     * @return Record
     */
    public function create(string $model, array $data = [])
    {
        // Si l'enregistrement est un utilisateur
        if ('res.user' == $model) {
            throw new LogicException('Unable to create a user from API - To create new user please go to Odoo configuration panel');
        }

        // Création de l'enregitrement et récupération de l'ID
        $id = $this->client->create($model, $data);

        // Récupération de l'enregistrement enregistré
        $record = $this->get($model, $id);

        // Enregistrement des données originelles
        $this->setOriginalData($model, $id, $record->getData());

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Update a record.
     *
     * @param Record $record
     *
     * @throws LogicException when a record cannot be created
     *
     * @return Record
     */
    public function update(Record $record)
    {
        // Récupération du nom du modèle de l'enregistrement
        $model = $record->getModel();

        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Récupération des changements
        $changeSet = $this->getChangeSet($record);

        // Si pas de changement
        if (!$changeSet) {
            // Retour de l'enregistrement
            return $record;
        }

        // Mise-à-jour de l'enregistrement
        $this->client->update($model, $id, $changeSet);

        // Mise-à-jour des données originelles
        $this->setOriginalData($model, $id, $record->getData());

        // Retour de l'enregistrement identifié
        return $record;
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

        // Retour de la suppression par l'ID
        return $this->deleteById($record->getModel(), $record->getId());
    }

    /**
     * Delete a record by model and ID.
     *
     * @param string    $model
     * @param array|int $ids
     *
     * @return self
     */
    public function deleteById(string $model, $ids)
    {
        // Lancement de la requête de suppression
        $this->client->delete($model, $ids);

        // Retour du manager
        return $this;
    }

    /**
     * Reload a record.
     *
     * @param Record $record
     *
     * @return Record|null
     */
    public function reload(Record $record)
    {
        // Récupération de l'enregistrement
        $refreshed = $this->find($record->getModel(), $record->getId());

        // Si pas d'enregistrement
        if (null === $refreshed) {
            // Pas d'enregistrement rechargé
            return null;
        }

        // Enregistrement des données originelles
        $this->setOriginalData($record->getModel(), $record->getId(), $refreshed->getData());

        // Retour de l'enregistrement mis-à-jour
        return $record
            ->setData($refreshed->getData())
        ;
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
     * @return Record
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
        $this->setOriginalData($model, $id, $record->getData());

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Find a record by model and ID.
     *
     * @param string $model
     * @param int    $id
     * @param array  $options
     *
     * @return Record|null
     */
    public function find(string $model, int $id, array $options = [])
    {
        return $this->findOneBy($model, [
            ['id', '=', $id],
            $options,
        ]);
    }

    /**
     * Find one record by record model, domains and options.
     *
     * @param string $model
     * @param array  $domains
     * @param array  $options
     *
     * @return Record|null
     */
    public function findOneBy(string $model, array $domains = [], array $options = [])
    {
        // Récupération des entrées
        $records = $this->client->searchAndRead($model, $domains, array_merge($options, [
            'limit' => 1,
        ]));

        // Retour de la dénormlisation de la lecture sur l'ID
        return $records ? $this->createRecord($model, $records[0]['id'], $records[0]) : null;
    }

    /**
     * Find records by record model, domains and options.
     *
     * @param string $model
     * @param array  $domains
     * @param array  $options
     *
     * @return Record[]
     */
    public function findBy(string $model, array $domains = [], array $options = [])
    {
        // Récupération des entrées
        $result = $this->client->searchAndRead($model, $domains, $options);

        // Pour chaque ligne de données
        foreach ($result as $key => $data) {
            // Création de l'enregistrement
            $record = $this->createRecord($model, $data['id'], $data);

            // Dénormalization des données
            $result[$key] = $record;
        }

        // Retour des données
        return $result;
    }

    /**
     * Get change set of a record.
     *
     * @param Record $record
     *
     * @return array
     */
    public function getChangeSet(Record $record)
    {
        // Récupération des données originelles
        $original = $this->getOriginalData($record->getModel(), $record->getId());

        // Si l'enregistrement ne possède pas de données originelles
        if (!$original) {
            // Retour de l'enregistrement normalisé
            return $record->getData();
        }

        // Retour de la différence entre les données originelles et l'enregistrement normalisé
        return $this->diff($record->getData(), $original);
    }

    /**
     * Create a new record.
     *
     * @param string $model
     * @param int    $id
     * @param array  $data
     *
     * @return Record
     */
    private function createRecord(string $model, int $id, array $data = [])
    {
        // Création de l'enregistrement
        $record = new Record($model, $id, $data);

        // Mise-à-jour des données originelles
        $this->setOriginalData($model, $id, $data);

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Hydrate original data of a record.
     *
     * @param Record $record
     * @param array  $data
     *
     * @return Record
     */
    private function hydrateOriginalData(Record $record, array $data = [])
    {
        // Réflection de la propriété des données originelles
        $property = new ReflectionProperty(Record::class, 'originalData');

        // On rend accessible la propriété
        $property->setAccessible(true);

        // Mise-à-jour de la valeur de la propriété
        $property->setValue($record, $data);

        // Retour de l'enregistrement
        return $record;
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
     * Set data for a model.
     *
     * @internal
     *
     * @param string $model
     * @param int    $id
     * @param array  $data
     *
     * @return self
     */
    private function setOriginalData($model, int $id, array $data = [])
    {
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
}
