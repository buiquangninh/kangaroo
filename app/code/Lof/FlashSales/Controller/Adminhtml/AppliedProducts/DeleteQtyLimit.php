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
use Magento\Framework\Controller\ResultInterface;

class DeleteQtyLimit extends AppliedProducts implements \Magento\Framework\App\Action\HttpPostActionInterface
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
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $error = false;
        $message = '';
        $model = $this->appliedProducts->load($id);
        if ($id) {
            try {
                $model->setData('qty_limit', 0)->save();
                $this->_productPriceIndexer->executeByFlashSalesId($model->getFlashsalesId());
                $message = __('You deleted the quantity limit.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the quantity limit right now.');
                $this->logger->critical($e);
            }
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
