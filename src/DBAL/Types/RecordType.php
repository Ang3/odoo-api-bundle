<?php

namespace Ang3\Bundle\OdooApiBundle\DBAL\Types;

use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
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
    const NAME = 'odoo_model_record';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritdoc}
     *
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // Si valeur nulle
        if (null === $value) {
            // Retour nul
            return null;
        }

        // Si la valeur est un entier
        if (is_int($value)) {
            // Retour de la valeur
            return $value;
        }

        // Si la valeur est une ManyToOne
        if ($value instanceof ManyToOne) {
            // Retour de l'identifiant
            return $value->getId();
        }

        // Si la valeur est une interface d'enregistrement
        if ($value instanceof RecordInterface) {
            // Retour de l'identifiant
            return $value->getId();
        }

        throw ConversionException::conversionFailedInvalidType($value, 'integer', [ManyToOne::class, RecordInterface::class]);
    }

    /**
     * {@inheritdoc}
     *
     * @return ManyToOne|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (int) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
