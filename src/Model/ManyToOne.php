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
     * @var string
     *
     * @JMS\Exclude
     */
    protected $class;

    /**
     * @var string
     *
     * @JMS\Exclude
     */
    protected $property;

    /**
     * @var string|null
     *
     * @JMS\Exclude
     */
    protected $target;

    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("id")
     */
    protected $id;

    /**
     * Create a new ManyToOne.
     *
     * @static
     *
     * @param array $params
     *
     * @return self
     */
    public static function create(array $params)
    {
        // Création de la relation
        $manyToOne = new static();

        // Hydratation
        $manyToOne->id = $params[0];
        $manyToOne->displayName = $params[1];

        // Retour de la relation
        return $manyToOne;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return string|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
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
        return
            null !== $this->class
            && null !== $this->property
            && null !== $this->target
            && null !== $this->id
        ;
    }
}
