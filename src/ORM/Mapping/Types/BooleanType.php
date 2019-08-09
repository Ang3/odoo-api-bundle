<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

/**
 * @author Joanis ROUANET
 */
class BooleanType extends AbstractType
{
    /**
     * {@inheritdoc}.
     */
    public static function getName()
    {
        return 'boolean';
    }

    /**
     * {@inheritdoc}.
     *
     * @return bool|null
     */
    public function convertToPhpValue($value, array $options = [])
    {
        return null !== $value ? (bool) $value : null;
    }

    /**
     * {@inheritdoc}.
     *
     * @return bool|null
     */
    public function convertToOdooValue($value, array $options = [])
    {
        return null !== $value ? (bool) $value : null;
    }
}
