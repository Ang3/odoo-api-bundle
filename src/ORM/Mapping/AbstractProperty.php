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
     * @var bool
     */
    private $nullable;

    /**
     * @param string $localName
     * @param string $remoteName
     * @param bool   $nullable
     */
    public function __construct(string $localName, string $remoteName, bool $nullable = true)
    {
        $this->localName = $localName;
        $this->remoteName = $remoteName;
        $this->nullable = $nullable;
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
     * @param bool $nullable
     *
     * @return self
     */
    public function setNullable(bool $nullable)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    public function isNullable()
    {
        return $this->nullable;
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
