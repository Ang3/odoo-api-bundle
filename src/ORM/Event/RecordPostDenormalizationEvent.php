<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\Manager;
use Ang3\Bundle\OdooApiBundle\ORM\NormalizationContext;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;

/**
 * @author Joanis ROUANET
 */
class RecordPostDenormalizationEvent extends RecordDenormalizationEvent
{
    /**
     * @var RecordInterface
     */
    private $record;

    /**
     * @param Manager              $manager
     * @param RecordInterface      $record
     * @param array                $data
     * @param string               $class
     * @param NormalizationContext $context
     */
    public function __construct(Manager $manager, RecordInterface $record, array $data, string $class, NormalizationContext $context)
    {
        // Construction de l'évènement de base
        parent::__construct($manager, $data, $class, $context);

        // Hydratation
        $this->record = $record;
    }

    /**
     * @param RecordInterface $record
     *
     * @return self
     */
    public function setRecord(RecordInterface $record)
    {
        $this->record = $record;

        return $this;
    }

    /**
     * @return RecordInterface
     */
    public function getRecord()
    {
        return $this->record;
    }
}
