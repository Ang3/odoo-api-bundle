<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Product;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use Ang3\Bundle\OdooApiBundle\ORM\Model\Record;
use Ang3\Bundle\OdooApiBundle\ORM\Model\NamedRecordTrait;

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
     * @ORM\Field(name="parent_path", type="string")
     */
    protected $parentPath;

    /**
     * @var int|null
     *
     * @ORM\ReadOnly
     * @ORM\Field(name="product_count", type="integer")
     */
    protected $nbProducts;

    /**
     * @var Category|null
     *
     * @ORM\ManyToOne(name="parent_id", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Product\Category")
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
