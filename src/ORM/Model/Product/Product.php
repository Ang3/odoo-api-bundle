<?php

namespace Ang3\Bundle\OdooApiBundle\ORM\Model\Product;

use Ang3\Bundle\OdooApiBundle\ORM\Annotation as ORM;
use JMS\Serializer\Annotation as JMS;

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
     * @JMS\Type("Ang3\Bundle\OdooApiBundle\ORM\Serializer\Type\SingleAssociation<'Ang3\Bundle\OdooApiBundle\ORM\Model\Product\Template'>")
     * @JMS\SerializedName("product_tmpl_id")
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
