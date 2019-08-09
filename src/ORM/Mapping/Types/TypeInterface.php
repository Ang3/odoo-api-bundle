<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

/**
 * @author Joanis ROUANET
 */
interface TypeInterface
{
    /**
     * Get the name of the type.
     *
     * @static
     *
     * @return string
     */
    public static function getName();

    /**
     * Convert odoo value to PHP value.
     *
     * @param mixed $value
     * @param array $options
     *
     * @return mixed
     */
    public function convertToPhpValue($value, array $options = []);

    /**
     * Convert PHP value to Odoo value.
     *
     * @param mixed $value
     * @param array $options
     *
     * @return mixed
     */
    public function convertToOdooValue($value, array $options = []);
}
