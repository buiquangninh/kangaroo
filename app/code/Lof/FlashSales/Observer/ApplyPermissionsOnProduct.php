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

namespace Lof\FlashSales\Observer;

use Lof\FlashSales\Helper\PermissionsData;
use Magento\Catalog\Model\Product;

class ApplyPermissionsOnProduct
{

    /**
     * @var PermissionsData
     */
    protected $_permissionsData;

    /**
     * ApplyPermissionsOnProduct constructor.
     * @param PermissionsData $permissionsData
     */
    public function __construct(
        PermissionsData $permissionsData
    ) {
        $this->_permissionsData = $permissionsData;
    }

    /**
     * Apply category related permissions on product
     *
     * @param Product $product
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($product)
    {

        if (!$this->_permissionsData->isAllowedCategoryView(null, $product)) {
            $product->setIsHidden(true);
        }

        if (!$this->_permissionsData->isAllowedProductPrice($product)) {
            $product->setCanShowPrice(false);
        }

        if (!$this->_permissionsData->isAllowedCheckoutItems($product)) {
            $product->setDisableAddToCart(true);
        }

        return $this;
    }
}
