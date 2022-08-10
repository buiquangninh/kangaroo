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

namespace Lof\FlashSales\Model\Plugin\Theme\Block\Html;

use Lof\FlashSales\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Theme\Block\Html\Topmenu as TopmenuBlock;

class Topmenu
{

    /**
     * Current customer session.
     *
     * @var Session
     */
    private $session;

    /**
     * Config Data.
     *
     * @var Data
     */
    private $helperData;

    /**
     * @param Data $helperData
     * @param Session $session
     */
    public function __construct(
        Data $helperData,
        Session $session
    ) {
        $this->helperData = $helperData;
        $this->session = $session;
    }

    /**
     * Plugin that generates unique block cache key depending on customer group.
     *
     * @param TopmenuBlock $block
     * @return null
     */
    public function beforeToHtml(TopmenuBlock $block)
    {
        if ($this->helperData->isEnabled()) {
            $customerGroupId = $this->session->getCustomerGroupId();
            $key = $block->getCacheKeyInfo();
            $key = array_values($key);
            $key[] = $customerGroupId;
            $key = implode('|', $key);
            $key = sha1($key);
            $block->setData('cache_key', $key);
        }

        return null;
    }
}
