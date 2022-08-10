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

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Helper\PermissionsData;
use Lof\FlashSales\Model\FlashSales;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ApplyProductPermissionOnCollectionAfterLoadObserver implements ObserverInterface
{

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var PermissionsData
     */
    protected $_permissionsData;

    /**
     * @var FlashSales
     */
    protected $_flashSalesModel;

    /**
     * @var ApplyPermissionsOnProduct
     */
    protected $applyPermissionsOnProduct;

    /**
     * ApplyProductPermissionObserver constructor.
     * @param Data $_helperData
     * @param PermissionsData $permissionsData
     * @param FlashSales $flashSalesModel
     * @param ApplyPermissionsOnProduct $applyPermissionsOnProduct
     */
    public function __construct(
        Data $_helperData,
        PermissionsData $permissionsData,
        FlashSales $flashSalesModel,
        ApplyPermissionsOnProduct $applyPermissionsOnProduct
    ) {
        $this->_permissionsData = $permissionsData;
        $this->applyPermissionsOnProduct = $applyPermissionsOnProduct;
        $this->_flashSalesModel = $flashSalesModel;
        $this->_helperData = $_helperData;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }
        $collection = $observer->getEvent()->getCollection();
        foreach ($collection as $product) {
            if ($collection->hasFlag('product_children')) {
                $product->addData(
                    [
                        'grant_event_category_view' => 1,
                        'grant_event_product_price' => 1,
                        'grant_checkout_items' => 1
                    ]
                );
            } else {
                $this->_flashSalesModel->addIndexToProduct($product);

            }
            if ($product->getData(FlashSalesInterface::IS_PRIVATE_SALE) != null &&
                $product->getData(FlashSalesInterface::IS_PRIVATE_SALE) == 1) {
                $this->applyPermissionsOnProduct->execute($product);
            }
        }

        return $this;
    }
}
