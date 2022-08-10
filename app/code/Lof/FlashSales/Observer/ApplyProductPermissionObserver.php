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

class ApplyProductPermissionObserver implements ObserverInterface
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
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $this->_flashSalesModel->addIndexToProduct($product);

        if ($product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG) == null) {
            return $this;
        }

        if ($product->getData(FlashSalesInterface::IS_PRIVATE_SALE) != 1) {
            return $this;
        }

        $this->applyPermissionsOnProduct->execute($product);
        if ($observer->getEvent()->getProduct()->getIsHidden()) {
            $observer->getEvent()->getControllerAction()->getResponse()->setRedirect(
                $this->_permissionsData->getLandingPageUrl(null, $product)
            );

            throw new \Magento\Framework\Exception\LocalizedException(
                __('You may need more permissions to access this product.')
            );
        }
        return $this;
    }
}
