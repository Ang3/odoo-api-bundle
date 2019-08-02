<?php

namespace Ang3\Bundle\OdooApiBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Joanis ROUANET
 */
class ManyToOne
{
    use DisplayedRecordTrait;

    /**
     * @var string|null
     *
     * @JMS\Exclude
     */
    protected $class;

    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("id")
     */
    protected $id;

    /**
     * @static
     *
     * @param string      $class
     * @param int|null    $id
     * @param string|null $displayName
     */
    public static function create(string $class = null, int $id = null, $displayName = null)
    {
        // Création de l'objet
        $manyToOne = new static();

        // Hydratation
        $manyToOne->class = $class;
        $manyToOne->id = $id;
        $manyToOne->displayName = $displayName;

        // Retour de l'objet
        return $manyToOne;
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Serialize the association.
     *
     * @return array
     */
    public function serialize()
    {
        return [$this->id, $this->displayName];
    }

    /**
     * Check if the association is loadable.
     *
     * @return bool
     */
    public function isLoadable()
    {
        return null !== $this->class && null !== $this->id;
    }
}
