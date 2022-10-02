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

namespace Magetop\Smtp\Block\Adminhtml\System\Config;

use Magetop\Smtp\Model\Config\Source\SyncType;

/**
 * Class Sync
 * @package Magetop\Smtp\Block\Adminhtml\System\Config
 */
class Sync extends Button
{
    /**
     * @var string
     */
    protected $_template = 'Magetop_Smtp::system/config/sync-template.phtml';

    /**
     * @return string
     */
    public function getEstimateUrl()
    {
        return $this->getUrl('adminhtml/smtp_sync/estimate', ['_current' => true]);
    }

    /**
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->getRequest()->getParam('website');
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }

    /**
     * @return array
     */
    public function getSyncSuccessMessage()
    {
        return [
            SyncType::CUSTOMERS   => __('Customer synchronization has been completed.'),
            SyncType::ORDERS      => __('Order synchronization has been completed.'),
            SyncType::SUBSCRIBERS => __('Subscriber synchronization has been completed.')
        ];
    }

    /**
     * @return string
     */
    public function getElementId()
    {
        return 'mp-synchronize';
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return 'Magetop_Smtp/js/sync/sync';
    }
}
