<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types;

use DateTime;
use DateTimeZone;
use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingTypeException;

/**
 * @author Joanis ROUANET
 */
class DateTimeType extends AbstractType
{
    /**
     * {@inheritdoc}.
     */
    public static function getName()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}.
     *
     * @return bool|null
     */
    public function convertToPhpValue($value, array $options = [])
    {
        // Si pas de valeur
        if (null === $value) {
            // Retour nul
            return null;
        }

        // Récupération des options de fuseaux horaires
        list($format, $timezone) = [
            !empty($options['format']) ? $options['format'] : 'Y-m-d H:i:s',
            !empty($options['timezone']) ? $options['timezone'] : 'UTC',
        ];

        // Si la valeur est déjà une date
        if ($value instanceof DateTime) {
            // Mise-à-jour du fuseau horaire
            $value->setTimezone(new DateTimeZone($timezone));

            // Retour de la valeur
            return $value;
        }

        // Si la valeur n'est pas non plus une chaîne de caractères
        if (!is_string($value)) {
            throw MappingTypeException::conversionFailedInvalidType($value, 'string', ['string', DateTime::class]);
        }

        // Retour de la création de la date depuis les paramètres
        return DateTime::createFromFormat($format, $value, $timezone);
    }

    /**
     * {@inheritdoc}.
     *
     * @return bool|null
     */
    public function convertToOdooValue($value, array $options = [])
    {
        // Si pas de valeur
        if (null === $value) {
            // Retour nul
            return null;
        }

        // Si la valeur est déjà une chaîne de caractères
        if (is_string($value)) {
            // Retour de la valeur
            return $value;
        }

        // Si la valeur n'est pas une date
        if (!($value instanceof DateTime)) {
            throw MappingTypeException::conversionFailedInvalidType($value, 'string', ['string', DateTime::class]);
        }

        // Récupération des options de fuseaux horaires
        list($format, $timezone) = [
            !empty($options['format']) ? $options['format'] : 'Y-m-d H:i:s',
            !empty($options['timezone']) ? $options['timezone'] : 'UTC',
        ];

        // Mise-à-jour du fuseau horaire et retour de la date
        return $value
            ->setTimezone(new DateTimeZone($timezone))
            ->format($format)
        ;
    }
}
