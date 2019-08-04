<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use JMS\Serializer\Annotation as JMS;
use Ang3\Bundle\OdooApiBundle\Model\DisplayedRecordTrait;

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
     * @param int|null $id
     *
     * @return self
     */
    public function setId(int $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
}
