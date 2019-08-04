<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class ManyToMany
{
    /**
     * @var string|null
     *
     * @JMS\Exclude
     */
    protected $class;

    /**
     * @var int[]
     *
     * @JMS\Type("array<int>")
     * @JMS\SerializedName("id")
     */
    protected $ids = [];

    /**
     * @static
     *
     * @param string $class
     * @param array  $ids
     */
    public static function create(string $class = null, array $ids = [])
    {
        // Création de l'objet
        $manyToOne = new static();

        // Hydratation
        $manyToOne->class = $class;
        $manyToOne->ids = $ids;

        // Retour de l'objet
        return $manyToOne;
    }

    /**
     * @param string|null $class
     *
     * @return self
     */
    public function setClass(string $class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param array $ids
     *
     * @return self
     */
    public function setIds(array $ids = [])
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function add(int $id)
    {
        // Si l'ID n'est pas déjà enregistré
        if (!in_array($id, $this->ids)) {
            // Enregistrement de l'ID
            $this->ids[] = $id;
        }

        // Retour de l'association
        return $this;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function remove(int $id)
    {
        // Tant qu'on a une clé pour l'ID
        while ($key = array_search($id, $this->ids)) {
            // Suppression de l'ID
            unset($this->ids[$key]);
        }

        // Retour de l'association
        return $this;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
