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

namespace Lof\FlashSales\Block\FlashSales\Category;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class EventBanner extends \Lof\FlashSales\Block\FlashSales\AbstractEvent
{

    /**
     * @return bool|mixed
     */
    public function canDisplay()
    {
        return $this->helperData->isEnabled() &&
            $this->getFlashSalesBanner() &&
            $this->getFlashSalesBanner()->getStatus() != \Lof\FlashSales\Model\FlashSales::STATUS_UPCOMING &&
            $this->getFlashSalesBanner()->getStatus() != \Lof\FlashSales\Model\FlashSales::STATUS_ENDED &&
            !!$this->getFlashSalesBanner()->getIsAssignCategory();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDefaultBanner()
    {
        if ($this->helperData->getDefaultBanner()) {
            return $this->helperData->getMediaBaseUrl() .
                'lofflashsales/display_settings/'
                . $this->helperData->getDefaultBanner();
        }

        return $this->getViewFileUrl('Lof_FlashSales::images/placeholder/default-banner.png');
    }

    /**
     * @param $flashSale
     * @param string $field
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getHeaderBannerImage($flashSale, $field = FlashSalesInterface::HEADER_BANNER_IMAGE)
    {
        if ($this->flashSaleImage->getUrl($flashSale, $field)) {
            return $this->flashSaleImage->getUrl($flashSale, $field);
        }

        return null;
    }
}
