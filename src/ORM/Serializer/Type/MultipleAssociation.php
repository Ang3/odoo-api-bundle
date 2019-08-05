<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type;

/**
 * @author Joanis ROUANET
 */
class MultipleAssociation
{
    /**
     * @var array
     */
    private $ids;

    /**
     * @param array $ids
     */
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
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
    public function addId(int $id)
    {
        if (!in_array($id, $this->ids)) {
            $this->ids[] = $id;
        }

        return $this;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function removeId(int $id)
    {
        if (in_array($id, $this->ids)) {
            unset($this->ids[$id]);
        }

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
