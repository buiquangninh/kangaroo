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

namespace Lof\FlashSales\Helper;

use Lof\FlashSales\Model\FlashSales;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory as AppliedProductsCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class CountDownData extends AbstractHelper
{

    /**
     * @var AppliedProductsCollectionFactory
     */
    private $appliedProductsCollectionFactory;

    /**
     * @var FlashSales
     */
    private $flashSalesModel;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * CountDownData constructor.
     * @param Context $context
     * @param Data $helperData
     * @param FlashSales $flashSalesModel
     * @param AppliedProductsCollectionFactory $appliedProductsCollectionFactory
     */
    public function __construct(
        Context $context,
        Data $helperData,
        FlashSales $flashSalesModel,
        AppliedProductsCollectionFactory $appliedProductsCollectionFactory
    ) {
        $this->helperData = $helperData;
        $this->flashSalesModel = $flashSalesModel;
        $this->appliedProductsCollectionFactory = $appliedProductsCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @param $childProductId
     * @return FlashSales
     */
    public function getFlashSalesWithChildProduct($childProductId)
    {
        $appliedProduct = $this->appliedProductsCollectionFactory->create()
            ->addFieldToFilter('product_id', $childProductId)
            ->addFieldToFilter('flash_sale_price', ['gteq' => 0])
            ->getFirstItem();
        if ($appliedProduct->getEntityId()) {
            $flashSale = $this->flashSalesModel->load($appliedProduct->getFlashSalesId());
            if ($this->eventTimingValid($flashSale)) {
                return $flashSale;
            }
        }
        return null;
    }

    /**
     * @param $productId
     * @return FlashSales
     * @throws NoSuchEntityException
     */
    public function getFlashSaleByProductId($productId)
    {
        $flashSale = $this->getFlashSalesWithChildProduct($productId);
        if (!$flashSale) {
            return null;
        }
        $flashSale->setData('format_time', $this->helperData->getProductTimerMode());
        $flashSale->setData('current_date_time', $this->getCurrentDateTimeFlashSales($flashSale));
        return $flashSale;
    }

    /**
     * @param $flashSale
     * @return string|null
     */
    public function getCurrentDateTimeFlashSales($flashSale)
    {
        $dataTime = null;
        if ($flashSale->getStatus()) {
            switch ($flashSale->getStatus()) {
                case 'comingsoon':
                    $dataTime = $flashSale->getFromDate();
                    break;
                case 'active':
                case 'endingsoon':
                    $dataTime = $flashSale->getToDate();
            }
        }
        return $dataTime;
    }

    /**
     * @param $flashSale
     * @return bool
     */
    public function eventTimingValid($flashSale)
    {
        $dataTime = $this->helperData->getCurrentDateTime();
        if ($flashSale->getFromDate() <= $dataTime && $flashSale->getToDate() >= $dataTime) {
            return true;
        }
        return false;
    }
}
