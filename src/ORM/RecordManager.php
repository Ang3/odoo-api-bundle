<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

use InvalidArgumentException;
use LogicException;
use Ang3\Component\OdooApiClient\ExternalApiClient;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\RecordNotFoundException;
use Ang3\Bundle\OdooApiBundle\ORM\Factory\ClassMetadataFactory;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Catalog;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\ClassMetadata;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\RecordNormalizer;
use Doctrine\Common\Util\ClassUtils;

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
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

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
     * @param ExternalApiClient    $client
     * @param ClassMetadataFactory $classMetadataFactory
     * @param Catalog              $catalog
     * @param RecordNormalizer     $normalizer
     */
    public function __construct(ExternalApiClient $client, ClassMetadataFactory $classMetadataFactory, Catalog $catalog, RecordNormalizer $normalizer)
    {
        $this->client = $client;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->unitOfWork = new UnitOfWork($client, $classMetadataFactory, $catalog, $normalizer);
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
        $this->unitOfWork->persist($record);

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
        $this->unitOfWork->delete($record);

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
        $this->unitOfWork->reload($record);

        return $record;
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
        return $this->classMetadataFactory->load($class);
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
            ->getUnitOfWOrk()
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
            ->getUnitOfWOrk()
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
     * @return ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        return $this->classMetadataFactory;
    }

    /**
     * @return UnitOfWork
     */
    public function getUnitOfWOrk()
    {
        return $this->unitOfWork;
    }
}
