<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Product;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;

/**
 * @ORM\Model("product.product")
 *
 * @author Joanis ROUANET
 */
class Product extends Template
{
    /**
     * @var Template
     *
     * @ORM\ManyToOne(name="product_tmpl_id", class="Ang3\Bundle\OdooApiBundle\ORM\Model\Product\Template", nullable=false)
     */
    protected $template;

    /**
     * @param Template $template
     *
     * @return self
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
