<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\Manager;
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
     * @param Manager         $manager
     * @param RecordInterface $record
     * @param array           $changeSet
     */
    public function __construct(Manager $manager, RecordInterface $record, array $changeSet)
    {
        // Hydratation
        $this->changeSet = $changeSet;

        // Construction de l'évènement parent
        parent::__construct($manager, $record);
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }
}
