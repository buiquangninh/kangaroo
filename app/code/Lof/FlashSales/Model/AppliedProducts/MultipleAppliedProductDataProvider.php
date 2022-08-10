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

namespace Lof\FlashSales\Model\AppliedProducts;

class MultipleAppliedProductDataProvider extends AppliedProductDataProvider
{

    protected $loadedData;

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $flashsalesId = $this->request->getParam('flashsales_id');
        $data['data'] = $this->request->getParam('data');
        $data['data']['flashsales_id'] = $this->request->getParam('flashsales_id');
        $data['data']['discount_amount'] = $this->request->getParam('discount_amount');
        $data['data']['form_key'] = $this->request->getParam('form_key');
        $data['data']['namespace'] = $this->request->getParam('namespace');
        if (null != $this->loadedData) {
            return $this->loadedData;
        }
        $this->loadedData[$flashsalesId] = $data;
        $this->loadedData[$flashsalesId] =$data['data'];
        return $this->loadedData;
    }
}
