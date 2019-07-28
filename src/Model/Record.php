<?php

namespace Ang3\Bundle\OdooApiBundle\Model;

/**
 * @author Joanis ROUANET
 */
class Record
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor of the record.
     *
     * @param string $model
     * @param int    $id
     */
    public function __construct(string $model, int $id, array $data = [])
    {
        $this->model = $model;
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode([$this->model, $this->id]);
    }

    /**
     * @param string $model
     *
     * @return self
     */
    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check if the record is new.
     *
     * @return bool
     */
    public function isNew()
    {
        return null === $this->id;
    }
}
