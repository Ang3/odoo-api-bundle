<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\RecordManager;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Joanis ROUANET
 */
class RecordEvent extends Event
{
    /**
     * @var RecordManager
     */
    private $recordManager;

    /**
     * @var RecordInterface
     */
    private $record;

    /**
     * @param RecordManager   $recordManager
     * @param RecordInterface $record
     */
    public function __construct(RecordManager $recordManager, RecordInterface $record)
    {
        $this->recordManager = $recordManager;
        $this->record = $record;
    }

    /**
     * @return RecordManager
     */
    public function getRecordManager()
    {
        return $this->recordManager;
    }

    /**
     * @return RecordInterface
     */
    public function getRecord()
    {
        return $this->record;
    }
}
