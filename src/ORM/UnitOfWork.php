<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use LogicException;
use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Catalog;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\RecordNormalizer;
use Ang3\Component\OdooApiClient\ExternalApiClient;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author Joanis ROUANET
 */
class UnitOfWork
{
    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @var Catalog
     */
    private $catalog;

    /**
     * @var RecordNormalizer
     */
    private $normalizer;

    /**
     * @var array
     */
    private $originalRecordData = [];

    /**
     * @var array
     */
    private $loadedRecordData = [];

    /**
     * Constructor of the manager.
     *
     * @param ExternalApiClient    $client
     * @param ClassMetadataFactory $classMetadataFactory
     * @param Catalog              $catalog
     * @param RecordNormalizer     $normalizer
     */
    public function __construct(ExternalApiClient $client, ClassMetadataFactory $classMetadataFactory, Catalog $catalog, RecordNormalizer $normalizer)
    {
        $this->client = $client;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->catalog = $catalog;
        $this->normalizer = $normalizer;
    }

    /**
     * Persist a record.
     *
     * @param RecordInterface $record
     *
     * @throws LogicException when the model is a user on creation
     */
    public function persist(RecordInterface $record)
    {
        // Récupération du nom du modèle de l'enregistrement
        $model = $this->catalog->getModel($record);

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
            $this->setOriginalRecordData($record, $data);
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
        $model = $this->catalog->getModel($class);

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
            $this->setOriginalRecordData($record);

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
        $model = $this->catalog->getModel($record);

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
        $original = $this->getOriginalRecordData($record);

        // Si l'enregistrement ne possède pas de données originelles
        if (!$original) {
            // Retour de l'enregistrement normalisé
            return $normalized;
        }

        // Retour de la différence entre les données originelles et l'enregistrement normalisé
        return $this->diff($normalized, $original);
    }

    /**
     * Delete a record.
     *
     * @param RecordInterface $record
     *
     * @return self
     */
    public function delete(RecordInterface $record)
    {
        // Relevé de l'ID de l'enregistrement
        $id = $record->getId();

        // Si pas d'ID
        if (null === $id) {
            // Retour du manager
            return $this;
        }

        // Récupéraion du nom du modèle
        $model = $this->catalog->getModel($record);

        // Lancement de la requête de suppression
        $this->client->delete($model, $id);

        // Relevé de l'ID de l'objet
        $objectId = spl_object_id($record);

        // Si on a des données pour ce mocèle identifié
        if (array_key_exists($objectId, $this->originalRecordData)) {
            // Suppression des données originelles stockées
            unset($this->originalRecordData[$objectId]);
        }

        // Suppression de l'identifiant
        $record->setId(null);

        // Retour de l'unité de travail
        return $this;
    }

    /**
     * Reload a record.
     *
     * @param RecordInterface $record
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

        // Création des métadonnées de la classe
        $classMetadata = $this->classMetadataFactory->load(ClassUtils::getClass($record));

        // Récupération de l'enregistrement
        $refreshed = $this->find($classMetadata->getName(), $id);

        // Si pas d'enregistrement
        if (null === $refreshed) {
            // Pas d'enregistrement rechargé
            return null;
        }

        // Pour chaque proprité de la classe
        foreach ($classMetadata->getProperties() as $property) {
            // On insère la valeur de l'enregistrement rafraichit dans la propriété de l'enregistrement mis-à-jour
            $property->setValue($record, $property->getValue($refreshed));
        }
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
    private function setOriginalRecordData(RecordInterface $record, array $data = [])
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
        $model = $this->catalog->getModel($record);

        // Enregistrement des données identifiées
        $this->originalRecordData[spl_object_id($record)] = $data ?: $this->normalizer->normalize($record);

        // Retour du cache
        return $this;
    }

    /**
     * Get cached data of a model.
     *
     * @internal
     *
     * @param RecordInterface $record
     *
     * @return array
     */
    private function getOriginalRecordData(RecordInterface $record)
    {
        // Récupération de l'identifiant de l'enregistrement
        $id = $record->getId();

        // Si pas d'identifiant
        if (null === $id) {
            // Retour d'un tableau vide
            return [];
        }

        // Récupéraion du nom du modèle
        $model = $this->catalog->getModel($record);

        // Relevé de l'ID de l'objet
        $objectId = spl_object_id($record);

        // Si on a des données pour ce mocèle identifié
        if (array_key_exists($objectId, $this->originalRecordData)) {
            // Retour des données
            return $this->originalRecordData[$objectId];
        }

        // Pas de données
        return [];
    }

    /**
     * Define record instance as loaded.
     *
     * @internal
     *
     * @param RecordInterface $record
     * @param bool|bool       $isLoaded
     */
    private function setLoaded(RecordInterface $record, bool $isLoaded = true)
    {
        // ...
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
     * @return ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        return $this->classMetadataFactory;
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
