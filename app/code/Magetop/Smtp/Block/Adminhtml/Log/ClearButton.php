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

namespace Magetop\Smtp\Block\Adminhtml\Log;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ClearButton
 * @package Magetop\Smtp\Block\Adminhtml\Log
 */
class ClearButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * ClearButton constructor.
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label'      => __('Clear All Logs'),
            'class'      => 'primary',
            'on_click'   => 'deleteConfirm(\'' . __(
                'Are you sure you want to clear all email logs?'
            ) . '\', \'' . $this->_urlBuilder->getUrl('*/*/clear') . '\')',
            'sort_order' => 10,
        ];
    }
}
