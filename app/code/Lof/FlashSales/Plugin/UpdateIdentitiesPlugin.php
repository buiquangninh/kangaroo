<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Plugin;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;

/**
 * Class UpdateIdentitiesPlugin to update identifiers for produced content with current category
 */
class UpdateIdentitiesPlugin
{

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Registry $coreRegistry
     */
    public function __construct(
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Update identifiers for produced content with current category
     *
     * @param View $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIdentities(
        View $subject,
        array $result
    ) {
        $category = $this->coreRegistry->registry('current_category');
        if ($category) {
            $result[] = Category::CACHE_TAG . '_' . $category->getId();
        }
        return $result;
    }
}
