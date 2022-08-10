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

namespace Lof\FlashSales\Controller\Adminhtml\AppliedProducts;

use Magento\Bundle\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class MassMultipleUpdate extends AppliedProducts implements \Magento\Framework\App\Action\HttpPostActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Lof_FlashSales::flashsales_update';

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws LocalizedException
     */
    public function processAndSaveData($postData)
    {
        $error = false;
        $discountAmount = $this->getRequest()->getParam('discount_amount');
        $qtyLimit = $this->getRequest()->getParam('qty_limit');
        $priceType = $this->getRequest()->getParam('price_type');

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            $collectionSize = $collection->getSize();
            foreach ($collection as $appliedProductsModel) {
                if ($appliedProductsModel->getFlashSalesId() == $postData['flashsales_id']) {
                    $productType = $appliedProductsModel->getTypeId();
                    if ($productType != Configurable::TYPE_CODE &&
                        $productType != Grouped::TYPE_CODE &&
                        $productType != Type::TYPE_CODE) {
                        $originalPrice = $appliedProductsModel->getOriginalPrice();
                        $flashSalePrice = $this->helperData->calcPriceRule($priceType, $discountAmount, $originalPrice);
                        $appliedProductsModel->setData('flash_sale_price', $flashSalePrice);
                        $appliedProductsModel->setData('discount_amount', $discountAmount);
                        $appliedProductsModel->setData('price_type', $priceType);
                        $appliedProductsModel->setData('qty_limit', $qtyLimit);
                        if (!$originalPrice) {
                            $appliedProductsModel->setData('original_price', null);
                        }
                        $appliedProductsModel->save();
                    }
                }
            }
            $this->_productPriceIndexer->executeByFlashSalesId($postData['flashsales_id']);
            $message = __('A total of %1 record(s) have been save.', $collectionSize);
        } catch (NoSuchEntityException $e) {
            $message = __('There is no such discount amount & qty limit entity to update.');
            $error = true;
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $message = __('We can\'t mass update the discount amount & qty limit right now.');
            $error = true;
            $this->logger->critical($e);
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
