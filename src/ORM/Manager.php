<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use InvalidArgumentException;
use LogicException;
use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\RecordNotFoundException;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Joanis ROUANET
 */
class Manager
{
    /**
     * @var ExternalApiClient
     */
    private $client;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * Original data of loaded records.
     *
     * @var array
     */
    private static $cache = [];

    /**
     * Constructor of the manager.
     *
     * @param ExternalApiClient        $client
     * @param Configuration            $configuration
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ExternalApiClient $client, Configuration $configuration, EventDispatcherInterface $eventDispatcher)
    {
        $this->client = $client;
        $this->configuration = $configuration;
        $this->unitOfWork = new UnitOfWork($this, $eventDispatcher);
    }

    /**
     * Normalize a record.
     *
     * @param RecordInterface           $record
     * @param NormalizationContext|null $context
     *
     * @return array
     */
    public function normalize(RecordInterface $record, NormalizationContext $context = null)
    {
        return $this
            ->getConfiguration()
            ->getNormalizer()
            ->toArray($record, $context)
        ;
    }

    /**
     * Denormalize a record.
     *
     * @param array                     $data
     * @param string                    $class
     * @param NormalizationContext|null $context
     *
     * @return RecordInterface
     */
    public function denormalize(array $data = [], string $class, NormalizationContext $context = null)
    {
        // Si la classe n'est pas managée
        if (!$this->isManagedClass($class)) {
            throw new InvalidArgumentException(sprintf('The class "%s" is not managed', $class));
        }

        /**
         * @var RecordInterface
         */
        $record = $this
            ->getConfiguration()
            ->getNormalizer()
            ->fromArray($data, $class, $context)
        ;

        // Retour de l'enregistrement
        return $record;
    }

    /**
     * Persist a record.
     *
     * @param Record $record
     *
     * @throws LogicException when a record cannot be created
     *
     * @return self
     */
    public function persist(RecordInterface $record)
    {
        // On persiste
        $this->unitOfWork->persist($record);

        // Retour du manager
        return $this;
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
        // On supprime l'enregistrement
        $this->unitOfWork->delete($record);

        // Retour du manager
        return $this;
    }

    /**
     * Reload a record.
     *
     * @param RecordInterface $record
     *
     * @return self
     */
    public function reload(RecordInterface $record)
    {
        $this->unitOfWork->reload($record);

        // Retour du manager
        return $this;
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
        return $this->unitOfWork->findBy($class, $domains, $options);
    }

    /**
     * Get class metadata of a record object or class.
     *
     * @param object|string $record
     *
     * @throws InvalidArgumentException when the record object or class is not managed
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($record)
    {
        // Définition de la classe de l'enregistrement
        $class = is_object($record) ? ClassUtils::getClass($record) : $record;

        // Si l'enregistrement ou sa classe ne sont pas managés
        if (!$this->isManagedClass($class)) {
            throw new InvalidArgumentException(sprintf('The class "%s" is not managed', $class));
        }

        // Retour de la création des métadonnées de la classe
        return $this
            ->getConfiguration()
            ->getClassMetadataFactory()
            ->load($class)
        ;
    }

    /**
     * Check if a class is managed.
     *
     * @param string $class
     *
     * @return bool
     */
    public function isManagedClass(string $class)
    {
        return $this
            ->getConfiguration()
            ->getCatalog()
            ->hasClass($class)
        ;
    }

    /**
     * Check if a class is managed.
     *
     * @param string $model
     *
     * @return bool
     */
    public function isManagedModel(string $model)
    {
        return $this
            ->getConfiguration()
            ->getCatalog()
            ->hasModel($model)
        ;
    }

    /**
     * @return ExternalApiClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWork()
    {
        return $this->unitOfWork;
    }
}
