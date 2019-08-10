<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\Manager;
use Ang3\Bundle\OdooApiBundle\ORM\NormalizationContext;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;

/**
 * @author Joanis ROUANET
 */
class RecordNormalizationEvent extends RecordEvent
{
    /**
     * @var NormalizationContext
     */
    protected $context;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param Manager              $manager
     * @param RecordInterface      $record
     * @param NormalizationContext $context
     * @param array                $data
     */
    public function __construct(Manager $manager, RecordInterface $record, NormalizationContext $context, array $data = [])
    {
        // Construction de l'évènement de base
        parent::__construct($manager, $record);

        // Hydratation
        $this->data = $data;
        $this->context = $context;
    }

    /**
     * @param NormalizationContext $context
     *
     * @return self
     */
    public function setContext(NormalizationContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return NormalizationContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
