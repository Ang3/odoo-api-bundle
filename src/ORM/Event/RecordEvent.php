<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\Manager;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Joanis ROUANET
 */
class RecordEvent extends Event
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var RecordInterface
     */
    private $record;

    /**
     * @param Manager         $manager
     * @param RecordInterface $record
     */
    public function __construct(Manager $manager, RecordInterface $record)
    {
        $this->manager = $manager;
        $this->record = $record;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @return RecordInterface
     */
    public function getRecord()
    {
        return $this->record;
    }
}
