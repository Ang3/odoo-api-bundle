<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Mapping;

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
     * @param string $localName
     * @param string $remoteName
     */
    public function __construct(string $localName, string $remoteName)
    {
        $this->localName = $localName;
        $this->remoteName = $remoteName;
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
