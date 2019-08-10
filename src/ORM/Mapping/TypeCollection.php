<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Ang3\Bundle\OdooApiBundle\ORM\Exception\MappingException;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types\TypeInterface;
use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types as Types;

/**
 * @author Joanis ROUANET
 */
class TypeCollection
{
    /**
     * @var TypeInterface[]
     */
    private $types = [];

    /**
     * Constructor of the collection with default types.
     */
    public function __construct()
    {
        // Hydratation des types par défaut
        $this->types[Types\BooleanType::getName()] = new Types\BooleanType();
        $this->types[Types\IntegerType::getName()] = new Types\IntegerType();
        $this->types[Types\FloatType::getName()] = new Types\FloatType();
        $this->types[Types\StringType::getName()] = new Types\StringType();
        $this->types[Types\DateTimeType::getName()] = new Types\DateTimeType();
        $this->types[Types\ManyToOneType::getName()] = new Types\ManyToOneType();
    }

    /**
     * Register a new type.
     *
     * @param TypeInterface $type
     *
     * @return self
     */
    public function register(TypeInterface $type)
    {
        // Enregistrement du type
        $this->types[$type::getName()] = $type;

        // Retour de la collection
        return $this;
    }

    /**
     * Remove a type.
     *
     * @param string $name
     *
     * @return self
     */
    public function remove(string $name)
    {
        // Si on a le type
        if ($this->has($name)) {
            // Suppression du type
            unset($this->types[$name]);
        }

        // Retour de la collection
        return $this;
    }

    /**
     * Get a type by name.
     *
     * @param string $name
     *
     * @throws MappingException When the type was not found
     *
     * @return TypeInterface
     */
    public function get(string $name)
    {
        // Si on a pas le type
        if (!$this->has($name)) {
            throw new MappingException(sprintf('Mapping type "%s" not found', $name));
        }

        // Retour de la collection
        return $this->types[$name];
    }

    /**
     * Check if a type is registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->types);
    }
}
