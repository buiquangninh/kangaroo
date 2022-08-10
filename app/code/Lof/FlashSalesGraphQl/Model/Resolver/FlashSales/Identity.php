<?php

namespace Lof\FlashSalesGraphQl\Model\Resolver\FlashSales;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Model\FlashSales;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved Flash Sales Events
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = FlashSales::CACHE_TAG;
    private $cacheTagCategory =  Category::CACHE_TAG;
    private $cacheTagCategoryProduct =  Product::CACHE_PRODUCT_CATEGORY_TAG;
    /**
     * Get Flash Sales ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $resolvedFlashSales = $resolvedData['items'] ?? $resolvedData;
        if (!empty($resolvedFlashSales)) {
            foreach ($resolvedFlashSales as $flashSales) {
                $ids[] = sprintf('%s_%s', $this->cacheTag, $flashSales[FlashSalesInterface::FLASHSALES_ID]);
                $ids[] = sprintf('%s_%s', $this->cacheTagCategory, $flashSales[FlashSalesInterface::CATEGORY_ID]);
                $ids[] = sprintf('%s_%s', $this->cacheTagCategoryProduct, $flashSales[FlashSalesInterface::CATEGORY_ID]);
            }
            if (!empty($ids)) {
                array_unshift($ids, $this->cacheTag);
            }
        }
        return $ids;
    }
}
