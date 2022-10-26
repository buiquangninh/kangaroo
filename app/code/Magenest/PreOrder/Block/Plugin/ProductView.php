<?php
namespace Magenest\PreOrder\Block\Plugin;

class ProductView extends \Magento\CatalogInventory\Block\Plugin\ProductView
{
    public function afterGetQuantityValidators(\Magento\Catalog\Block\Product\View $block, array $validators)
    {
        $result = parent::afterGetQuantityValidators($block, $validators);
        if ($block->getProduct()->getData('is_preorder')
            && (!isset($result['validate-item-quantity']['maxAllowed']) || $result['validate-item-quantity']['maxAllowed'] > 0)) {
            $result['validate-item-quantity']['maxAllowed'] = 1.0;
        }

        return $result;
    }
}
