<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Product;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Model("product.category")
 *
 * @author Joanis ROUANET
 */
class Category extends Record
{
    use NamedRecordTrait;

    /**
     * @var string|null
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("parent_path")
     */
    protected $parentPath;

    /**
     * @var int|null
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("product_count")
     */
    protected $nbProducts;

    /**
     * @var Category|null
     *
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Product\Category'>")
     * @JMS\SerializedName("parent_id")
     */
    protected $parent;

    /**
     * @return int
     */
    public function getNbProducts()
    {
        return $this->nbProducts ?: 0;
    }

    /**
     * @param string|null $parentPath
     *
     * @return self
     */
    public function setParentPath(string $parentPath = null)
    {
        $this->parentPath = $parentPath;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentPath()
    {
        return $this->parentPath;
    }

    /**
     * @param Category|null $parent
     *
     * @return self
     */
    public function setParent(Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Category|null
     */
    public function getParent()
    {
        return $this->parent;
    }
}
