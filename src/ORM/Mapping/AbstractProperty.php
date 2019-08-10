<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

use Ang3\Bundle\OdooApiBundle\ORM\Mapping\Types\TypeInterface;

/**
 * @abstract
 *
 * @author Joanis ROUANET
 */
abstract class AbstractProperty implements PropertyInterface
{
    /**
     * @var string
     */
    private $localName;

    /**
     * @var string
     */
    private $remoteName;

    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string        $localName
     * @param string        $remoteName
     * @param TypeInterface $type
     * @param array         $options
     */
    public function __construct(string $localName, string $remoteName, TypeInterface $type, array $options = [])
    {
        $this->localName = $localName;
        $this->remoteName = $remoteName;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * @param string $localName
     *
     * @return self
     */
    public function setLocalName(string $localName)
    {
        $this->localName = $localName;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * @param string $remoteName
     *
     * @return self
     */
    public function setRemoteName(string $remoteName)
    {
        $this->remoteName = $remoteName;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getRemoteName()
    {
        return $this->remoteName;
    }

    /**
     * @param TypeInterface $type
     *
     * @return self
     */
    public function setType(TypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}.
     */
    public function isReadOnly()
    {
        return array_key_exists('read_only', $this->options) && true === $this->options['read_only'];
    }

    /**
     * {@inheritdoc}.
     */
    public function isNullable()
    {
        return array_key_exists('nullable', $this->options) && true === $this->options['nullable'];
    }

    /**
     * {@inheritdoc}.
     */
    public function isField()
    {
        return true;
    }

    /**
     * {@inheritdoc}.
     */
    public function isAssociation()
    {
        return false;
    }
}
