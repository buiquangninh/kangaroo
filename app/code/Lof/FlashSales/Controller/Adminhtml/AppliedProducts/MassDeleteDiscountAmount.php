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

use Magento\Framework\Exception\LocalizedException;

class MassDeleteDiscountAmount extends AppliedProducts implements \Magento\Framework\App\Action\HttpPostActionInterface
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
     * @param $postData
     * @return \Magento\Framework\Phrase
     * @throws LocalizedException
     */
    public function processAndSaveData($postData)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $appliedProductsModel) {
            if ($appliedProductsModel->getFlashSalesId() == $postData['flashsales_id']) {
                $appliedProductsModel->setData('flash_sale_price', null)->save();
            }
        }
        $this->_productPriceIndexer->executeByFlashSalesId($postData['flashsales_id']);
        return __('A total of %1 record(s) have been deleted.', $collectionSize);
    }
}
