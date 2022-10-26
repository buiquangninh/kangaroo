<?php

namespace Magenest\CustomCatalog\Plugin;

class Toolbar
{
    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     */
    public function beforeGetCurrentDirection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject)
    {
        if ($subject->getCurrentOrder() == 'position' && !$subject->getData('_current_grid_direction')) {
            $subject->setData('_current_grid_direction', 'desc');
        }
    }
}