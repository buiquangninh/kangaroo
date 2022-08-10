<?php

namespace Lof\FlashSalesGraphQl\Model\Resolver\FlashSale;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Model\FlashSales;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS page
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = FlashSales::CACHE_TAG;
    private $cacheTagCategory =  Category::CACHE_TAG;
    private $cacheTagCategoryProduct =  Product::CACHE_PRODUCT_CATEGORY_TAG;

    /**
     * Get page ID from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData[FlashSalesInterface::FLASHSALES_ID]) ?
            [] : [
                $this->cacheTag,
                sprintf('%s_%s', $this->cacheTag, $resolvedData[FlashSalesInterface::FLASHSALES_ID]),
                sprintf('%s_%s', $this->cacheTagCategory, $resolvedData[FlashSalesInterface::CATEGORY_ID]),
                sprintf('%s_%s', $this->cacheTagCategoryProduct, $resolvedData[FlashSalesInterface::CATEGORY_ID]),
            ];
    }
}
