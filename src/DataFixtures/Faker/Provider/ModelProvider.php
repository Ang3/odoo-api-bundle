<?php

namespace Ang3\Bundle\OdooApiBundle\DataFixtures\Faker\Provider;

use ReflectionClass;
use Ang3\Bundle\OdooApiBundle\Model\RecordInterface;
use Ang3\Bundle\OdooApiBundle\ORM\ModelRegistry;
use Faker\Provider\Base as BaseProvider;

/**
 * @author Joanis ROUANET
 */
final class ModelProvider extends BaseProvider
{
    /**
     * @var ModelRegistry
     */
    private $modelRegistry;

    /**
     * @static
     *
     * @var int
     */
    private static $count = 0;

    /**
     * @required
     *
     * @param ModelRegistry $modelRegistry
     */
    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * @param string   $name
     * @param int|null $id
     *
     * @return RecordInterface
     */
    public function odooRecord(string $name, int $id = null)
    {
        // Création d'une réflection de la classe
        $reflection = new ReflectionClass($this->modelRegistry->getClass($name));

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
