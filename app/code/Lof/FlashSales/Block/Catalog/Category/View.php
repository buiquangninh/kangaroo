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

namespace Lof\FlashSales\Block\Catalog\Category;

class View extends \Magento\Catalog\Block\Category\View
{

    const DM_LOF_FLASHSALES = 'FLASHSALES';

    /**
     * @return $this|View
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock(\Magento\Catalog\Block\Breadcrumbs::class);
        $category = $this->getCurrentCategory();

        if ($category && $this->isFlashSalesMode()) {
            $title = $category->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = $category->getMetaDescription();
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            if ($this->_categoryHelper->canUseCanonicalTag()) {
                $this->pageConfig->addRemotePageAsset(
                    $category->getUrl(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }
            $this->getLayout()->unsetElement('catalog.leftnav');

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $this->getLayout()->unsetElement('page.main.title');
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFlashSalesListHtml()
    {
        return $this->getChildHtml('flashsales_list');
    }

    /**
     * Check if category display mode is "Flash Sale & Private Sale Event Page"
     * For anchor category with applied filter Static Block Only mode not allowed
     *
     * @return bool
     */
    public function isFlashSalesMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() === self::DM_LOF_FLASHSALES;
    }
}
