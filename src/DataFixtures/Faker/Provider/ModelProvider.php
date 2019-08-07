<?php

namespace Ang3\Bundle\OdooApiBundle\DataFixtures\Faker\Provider;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Catalog;
use Ang3\Bundle\OdooApiBundle\ORM\Model\RecordInterface;
use Faker\Provider\Base as BaseProvider;

/**
 * @author Joanis ROUANET
 */
final class ModelProvider extends BaseProvider
{
    /**
     * @var Catalog
     */
    private $catalog;

    /**
     * @static
     *
     * @var int
     */
    private static $count = 0;

    /**
     * @required
     *
     * @param Catalog $catalog
     */
    public function setCatalog(Catalog $catalog)
    {
        $this->catalog = $catalog;
    }

    /**
     * @param string   $class
     * @param int|null $id
     *
     * @return RecordInterface
     */
    public function odooRecord(string $class, int $id = null)
    {
        // Création d'une réflection de la classe
        $reflection = new ReflectionClass($class);

        // Création de l'enregistrement
        $record = $reflection->newInstanceWithoutConstructor();

        // Définition de l'ID de l'enregistrement
        $id = null !== $id ? $id : rand(self::$count, 999999999);

        // Incrémentation du compteur
        ++self::$count;

        // Assignation de l'ID de l'enregistrement
        $record->setId($id);

        // Retour de l'enregistrement
        return $record;
    }
}