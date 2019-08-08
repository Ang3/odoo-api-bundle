<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;

/**
 * @author Joanis ROUANET
 */
class RecordUpdateEvent extends RecordEvent
{
    /**
     * @var array
     */
    private $changeSet;

    /**
     * @param RecordManager   $recordManager
     * @param RecordInterface $record
     * @param array           $changeSet
     */
    public function __construct(RecordManager $recordManager, RecordInterface $record, array $changeSet)
    {
        // Hydratation
        $this->changeSet = $changeSet;

        // Construction de l'évènement parent
        parent::__construct($recordManager, $record);
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }
}
