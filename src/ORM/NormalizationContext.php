<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

/**
 * @author Joanis ROUANET
 */
class NormalizationContext
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
