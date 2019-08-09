<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

/**
 * @author Joanis ROUANET
 */
class StringType extends AbstractType
{
    /**
     * {@inheritdoc}.
     */
    public static function getName()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}.
     *
     * @return string|null
     */
    public function convertToPhpValue($value, array $options = [])
    {
        return null !== $value ? (string) $value : null;
    }

    /**
     * {@inheritdoc}.
     *
     * @return string|null
     */
    public function convertToOdooValue($value, array $options = [])
    {
        return null !== $value ? (string) $value : null;
    }
}
