<?php
namespace Magenest\PreOrder\Plugin;

use Magenest\PreOrder\Helper\PreOrderProduct;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product;

class ProductView
{
    /** @var PreOrderProduct */
    private $helper;

    /**
     * @param PreOrderProduct $helper
     */
    public function __construct(PreOrderProduct $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param View $subject
     * @param Product $result
     * @return Product
     */
    public function afterGetProduct(View $subject, Product $result): Product
    {
        if (empty($result->getData('add_to_cart_frontend_label'))) {
            if ($this->helper->isPreOrderProduct($result)) {
                $result->setData('is_preorder', 1);
                $result->setData('add_to_cart_frontend_label', $this->helper->getAddToCartLabel($result));
            } else {
                $result->setData('is_preorder', 0);
            }
        }

        return $result;
    }
}
