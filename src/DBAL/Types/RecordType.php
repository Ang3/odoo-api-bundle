<?php

namespace Ang3\Bundle\OdooApiBundle\DBAL\Types;

use Ang3\Bundle\OdooApiBundle\Model\Record;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * @author Joanis ROUANET
 */
class RecordType extends Type
{
    /**
     * Type name.
     */
    const NAME = 'odoo_record';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException when the value is not a record
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // Si valeur nulle
        if (null === $value) {
            // Retour nul
            return null;
        }

        // Si la valeur n'est pas une relation simple
        if (!($value instanceof Record)) {
            throw ConversionException::conversionFailedInvalidType($value, self::NAME, [Record::class]);
        }

        // Retour du JSON
        return json_encode([$value->getModel(), $value->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Si valeur nulle
        if (null === $value) {
            // Retour nul
            return null;
        }

        // Décoage des paramètres
        $params = json_decode($value, true);

        // Retour des paramètres
        return new Record($params[0], $params[1]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
