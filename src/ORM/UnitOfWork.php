<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use LogicException;
use Ang3\Bundle\OdooApiBundle\ORM\Event\RecordEvent;
use Ang3\Bundle\OdooApiBundle\ORM\Event\RecordUpdateEvent;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\RecordNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Joanis ROUANET
 */
class UnitOfWork
{
    /**
     * @var RecordManager
     */
    private $recordManager;

    /**
     * @var RecordNormalizer
     */
    private $recordNormalizer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $originalRecordData = [];

    /**
     * @var array
     */
    private $loadedRecordData = [];

    /**
     * @param RecordManager            $recordManager
     * @param RecordNormalizer         $recordNormalizer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(RecordManager $recordManager, RecordNormalizer $recordNormalizer, EventDispatcherInterface $eventDispatcher)
    {
        $this->recordManager = $recordManager;
        $this->recordNormalizer = $recordNormalizer;
        $this->eventDispatcher = $eventDispatcher;
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
        $model = $this->recordManager
            ->getCatalog()
            ->getModel($record)
        ;

        // Récupération de l'ID de l'enregistrement
        $id = $record->getId();

        // Si l'enregistrement ne possède pas encore d'identifiant
        if (null === $id) {
            // Si l'enregistrement est un utilisateur
            if ('res.user' == $model) {
                throw new LogicException('Unable to create a user from API - To create new user please go to Odoo configuration panel!');
            }

            // On dispatche l'évènement de pré-création
            $this->eventDispatcher->dispatch(Events::prePersist, new RecordEvent($this->recordManager, $record));

            // Création de l'enregitrement et récupération de l'ID
            $id = $this->recordManager
                ->getClient()
                ->create($model, $data = $this->recordNormalizer->normalize($record))
            ;

            // Enregistrement de l'ID de l'enregistrement
            $record->setId($id);

            // Enregistrement des données originelles
            $this->setOriginalRecordData($record, $data);

            // On dispatche l'évènement de post-création
            $this->eventDispatcher->dispatch(Events::postPersist, new RecordEvent($this->recordManager, $record));
        } else {
            // Récupération des changements
            $changeSet = $this->getChangeSet($record);

            // Si pas de changement
            if (!$changeSet) {
                // Retour de l'enregistrement
                return $record;
            }

            // On dispatche l'évènement de pré-mise-à-jour
            $this->eventDispatcher->dispatch(Events::preUpdate, new RecordUpdateEvent($this->recordManager, $record, $changeSet));

            // Mise-à-jour de l'enregistrement
            $this->recordManager
                ->getClient()
                ->update($model, $id, $changeSet)
            ;

            // On dispatche l'évènement de post-mise-à-jour
            $this->eventDispatcher->dispatch(Events::postUpdate, new RecordUpdateEvent($this->recordManager, $record, $changeSet));
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
        $model = $this->recordManager
            ->getCatalog()
            ->getModel($class)
        ;

        // Normalisation des domaines
        $domains = $this->recordNormalizer->normalizeDomains($class, $domains);

        // Récupération du noms des propriétés et leur nom sérialisés
        $serializedNames = $this->recordNormalizer->getSerializedNames($class);

        // Si pas d'options de champs particuliers
        if (!isset($options['fields'])) {
            // On filtre selon les champs de la classe
            $options['fields'] = array_values($serializedNames);
        }

        // Récupération des entrées
        $result = $this->recordManager
            ->getClient()->searchAndRead($model, $domains, $options);

        // Pour chaque ligne de données
        foreach ($result as $key => $data) {
            // Création de l'enregistrement
            $record = $this->recordNormalizer->denormalize($data, $class);

            // Mise-à-jour des données originelles
            $this->setOriginalRecordData($record);

            // On dispatche l'évènement de post-chargement
            $this->eventDispatcher->dispatch(Events::postLoad, new RecordEvent($this->recordManager, $record));

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
        $model = $this->recordManager
            ->getCatalog()
            ->getModel($record)
        ;

        // Normalization de l'enregistrement reçu
        $normalized = $this->recordNormalizer->normalize($record);

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
        $model = $this->recordManager
            ->getCatalog()
            ->getModel($record)
        ;

        // On dispatche l'évènement de pré-suppression
        $this->eventDispatcher->dispatch(Events::preDelete, new RecordEvent($this->recordManager, $record));

        // Lancement de la requête de suppression
        $this->recordManager
            ->getClient()->delete($model, $id);

        // Relevé de l'ID de l'objet
        $objectId = spl_object_id($record);

        // Si on a des données pour ce mocèle identifié
        if (array_key_exists($objectId, $this->originalRecordData)) {
            // Suppression des données originelles stockées
            unset($this->originalRecordData[$objectId]);
        }

        // Suppression de l'identifiant
        $record->setId(null);

        // On dispatche l'évènement de post-suppression
        $this->eventDispatcher->dispatch(Events::postDelete, new RecordEvent($this->recordManager, $record));

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
        $classMetadata = $this->recordManager->getClassMetadata($record);

        // Récupération de l'enregistrement
        $refreshed = $this->find($classMetadata->getClass(), $id);

        // Si pas d'enregistrement
        if (null === $refreshed) {
            // Pas d'enregistrement rechargé
            return null;
        }

        // Pour chaque proprité de la classe
        foreach ($classMetadata->iterateFields() as $name => $field) {
            // On insère la valeur de l'enregistrement rafraichit dans la propriété de l'enregistrement mis-à-jour
            $field->setValue($record, $field->getValue($refreshed));
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

        // Récupéraion du nom du modèle
        $model = $this->recordManager
            ->getCatalog()
            ->getModel($record)
        ;

        // Enregistrement des données identifiées
        $this->originalRecordData[spl_object_id($record)] = $data ?: $this->recordNormalizer->normalize($record);

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
        $model = $this->recordManager
            ->getCatalog()
            ->getModel($record)
        ;

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
     * @return RecordManager
     */
    public function getManager()
    {
        return $this->recordManager;
    }

    /**
     * @return RecordNormalizer
     */
    public function getNormalizer()
    {
        return $this->recordNormalizer;
    }
}
