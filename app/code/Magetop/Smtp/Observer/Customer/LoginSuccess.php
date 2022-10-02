<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Observer\Customer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type;
use Magetop\Smtp\Helper\Data;
use Magetop\Smtp\Helper\EmailMarketing;
use Zend_Cache;

/**
 * Class LoginSuccess
 * @package Magetop\Smtp\Observer\Customer
 */
class LoginSuccess implements ObserverInterface
{
    /**
     * @var Type
     */
    protected $fullPageCache;

    /**
     * @var EmailMarketing
     */
    protected $helperEmailMarketing;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * LoginSuccess constructor.
     *
     * @param Type $fullPageCache
     * @param Data $helperData
     * @param EmailMarketing $helperEmailMarketing
     */
    public function __construct(
        Type $fullPageCache,
        Data $helperData,
        EmailMarketing $helperEmailMarketing
    ) {
        $this->fullPageCache        = $fullPageCache;
        $this->helperData           = $helperData;
        $this->helperEmailMarketing = $helperEmailMarketing;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $scopeId = $this->helperData->getScopeId();
        } catch (Exception $e) {
            $scopeId = null;
        }

        if ($this->helperEmailMarketing->isEnableEmailMarketing($scopeId)) {
            $this->fullPageCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [EmailMarketing::CACHE_TAG]);
        }
    }
}
