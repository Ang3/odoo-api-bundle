<?php

namespace Ang3\Bundle\OdooApiBundle\DBAL\Types;

use Ang3\Bundle\OdooApiBundle\Manager\RecordManager;
use Ang3\Bundle\OdooApiBundle\Model\ManyToOne;
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
     * @var RecordManager
     */
    private $recordManager;

    /**
     * @required
     * 
     * @param RecordManager $recordManager
     */
    public function setRecordManager(RecordManager $recordManager)
    {
        $this->recordManager = $recordManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
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

        // Si la valeur est une ManyToOne
        if ($value instanceof ManyToOne) {
            // Récupération de l'ID de la relation
            $id = $value->getId();

            // Si pas d'ID
            if($id) {
                // Retour nul
                return null;
            }

            // Retour de l'identifiant de la relation
            return sprintf('%s,%s', $this->recordManager->getModelName($value->getTarget()), $value->getId());
        }

        // Retour de la valeur en chaine de caractères
        return substr((string) $value, 0, 255);
    }

    /**
     * {@inheritdoc}
     *
     * @return ManyToOne|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Si valeur nulle
        if (null === $value) {
            // Retour nul
            return null;
        }

        // Récupération des valeurs de l'identifiant
        $values = explode(',', $value);

        // Si on a moins de deux valeurs
        if(count($values) < 2) {
            // Pas d'ID
            return null;
        }

        // Récupération du modèle et de l'ID
        list($model, $id) = $values;

        // Si pas d'identifiant
        if(null === $id) {
            // Pas d'ID
            return null;
        }

        // Retour de la restauration de la relation
        return $this->recordManager->restoreManyToOne($model, $id);
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
