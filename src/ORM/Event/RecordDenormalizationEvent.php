<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Event;

use Ang3\Bundle\OdooApiBundle\ORM\Manager;
use Ang3\Bundle\OdooApiBundle\ORM\NormalizationContext;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Joanis ROUANET
 */
class RecordDenormalizationEvent extends Event
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $class;

    /**
     * @var NormalizationContext
     */
    private $context;

    /**
     * @param Manager              $manager
     * @param array                $data
     * @param string               $class
     * @param NormalizationContext $context
     */
    public function __construct(Manager $manager, array $data, string $class, NormalizationContext $context)
    {
        $this->manager = $manager;
        $this->data = $data;
        $this->class = $class;
        $this->context = $context;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
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

    /**
     * @param string $class
     *
     * @return self
     */
    public function setClass(string $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
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
}
