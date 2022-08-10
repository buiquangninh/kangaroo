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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends AppliedProducts implements \Magento\Framework\App\Action\HttpPostActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Lof_FlashSales::flashsales_save';

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function processAndSaveData($postData)
    {
        $id = $this->getRequest()->getParam('entity_id');
        $discountAmount = $this->getRequest()->getParam('discount_amount');
        $priceType = $this->getRequest()->getParam('price_type');
        $qtyLimit = $this->getRequest()->getParam('qty_limit');
        $position = $this->getRequest()->getParam('position');
        $error = false;
        try {
            $appliedProductsModel = $this->appliedProductsRepository->get($id);
            $originalPrice = $appliedProductsModel->getOriginalPrice();
            $flashSalePrice = $this->helperData->calcPriceRule($priceType, $discountAmount, $originalPrice);
            $appliedProductsModel->setDiscountAmount($discountAmount);
            $appliedProductsModel->setFlashSalePrice($flashSalePrice);
            $appliedProductsModel->setPriceType($priceType);
            $appliedProductsModel->setQtyLimit($qtyLimit);
            $appliedProductsModel->setPosition($position ?? 0);
            $this->appliedProductsRepository->save($appliedProductsModel);
            $this->_productPriceIndexer->executeByFlashSalesId($postData['flashsales_id']);
            $message = __('You saved discount amount the product.');
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
            $error = true;
            $message = __('This product no longer exists.');
        } catch (LocalizedException $e) {
            $error = true;
            $message = __($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $error = true;
            $message = __('Something went wrong while saving the product.');
            $this->logger->critical($e);
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'messages' => $message,
                'error' => $error,
                'data' => [
                    'entity_id' => $id
                ]
            ]
        );

        return $resultJson;
    }
}
