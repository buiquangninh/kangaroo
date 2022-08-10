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

namespace Lof\FlashSales\Model\Adminhtml\System\Config\Source;

use Lof\FlashSales\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\View\Asset\Repository;
use Psr\Log\LoggerInterface;

class CategoryHeaderStyle implements ArrayInterface
{

    /**
     * Header styles
     */
    const TYPE1 = 1;
    const TYPE2 = 2;
    const TYPE3 = 3;
    const TYPE4 = 4;

    /**
     * Asset service
     *
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $_request;

    /**
     * CategoryHeaderStyle constructor.
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param RequestInterface $request
     * @param Repository $assetRepo
     */
    public function __construct(
        Data $helperData,
        LoggerInterface $logger,
        RequestInterface $request,
        Repository $assetRepo
    ) {
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_assetRepo = $assetRepo;
        $this->helperData = $helperData;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $option = [
            [
                'value' => self::TYPE1,
                'label' => '<span>'.__('Template 1').'</span>
                <div><img src="'.$this->getViewFileUrl('Lof_FlashSales::images/category/category-style1.png').'"></div>'
            ],
            [
                'value' => self::TYPE2,
                'label' => '<span>'.__('Template 2').'</span>
                <div><img src="'.$this->getViewFileUrl('Lof_FlashSales::images/category/category-style2.png').'"></div>'
            ],
            [
                'value' => self::TYPE3,
                'label' => '<span>'.__('Template 3').'</span>
                <div><img src="'.$this->getViewFileUrl('Lof_FlashSales::images/category/category-style3.png').'"></div>'
            ],
            [
                'value' => self::TYPE4,
                'label' => '<span>'.__('Template 4').'</span>
                <div><img src="'.$this->getViewFileUrl('Lof_FlashSales::images/category/category-style4.png').'"></div>'
            ]
        ];
        return $option;
    }

    /**
     * Get request
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->getRequest()->isSecure()], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return __('This no image longer exists');
        }
    }
}
