<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

/**
 * @author Joanis ROUANET
 */
class ArrayType extends AbstractType
{
    /**
     * {@inheritdoc}.
     */
    public static function getName()
    {
        return 'array';
    }

    /**
     * {@inheritdoc}.
     *
     * @return array|null
     */
    public function convertToPhpValue($value, array $options = [])
    {
        return null !== $value ? (array) $value : null;
    }

    /**
     * {@inheritdoc}.
     *
     * @return array|null
     */
    public function convertToOdooValue($value, array $options = [])
    {
        return null !== $value ? (array) $value : null;
    }
}
