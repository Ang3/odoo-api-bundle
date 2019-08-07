<?php

namespace Ang3\Bundle\OdooApiBundle\Doctrine\DBAL\Types;

use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation;
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

        // Si la valeur est une association simple
        if ($value instanceof SingleAssociation) {
            // Retour de l'identifiant
            return $value->getId();
        }

        // Si la valeur est une interface d'enregistrement
        if ($value instanceof RecordInterface) {
            // Retour de l'identifiant
            return $value->getId();
        }

        throw ConversionException::conversionFailedInvalidType($value, 'integer', [SingleAssociation::class, RecordInterface::class]);
    }

    /**
     * {@inheritdoc}
     *
     * @return int
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
