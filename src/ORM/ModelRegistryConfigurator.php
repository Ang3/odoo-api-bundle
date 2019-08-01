<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

/**
 * @author Joanis ROUANET
 */
class ModelRegistryConfigurator
{
    private $formatterManager;

    /**
     * @param EmailFormatterManager $formatterManager
     */
    public function __construct(EmailFormatterManager $formatterManager)
    {
        $this->formatterManager = $formatterManager;
    }

    /**
     * {@inherited}.
     */
    public function configure(ModelRegistry $registry)
    {
        
    }
}