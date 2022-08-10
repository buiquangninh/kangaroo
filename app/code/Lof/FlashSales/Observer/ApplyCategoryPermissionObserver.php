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

namespace Lof\FlashSales\Observer;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Helper\PermissionsData;
use Lof\FlashSales\Model\FlashSales;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

class ApplyCategoryPermissionObserver implements ObserverInterface
{

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var PermissionsData
     */
    protected $_permissionsData;

    /**
     * @var FlashSales
     */
    protected $_flashSalesModel;

    /**
     * Store manager instance
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Customer session instance
     *
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ApplyPermissionsOnCategory
     */
    protected $applyPermissionsOnCategory;

    /**
     * ApplyCategoryPermissionObserver constructor.
     * @param Data $_helperData
     * @param StoreManagerInterface $storeManager
     * @param PermissionsData $permissionsData
     * @param Session $customerSession
     * @param FlashSales $flashSalesModel
     * @param ApplyPermissionsOnCategory $applyPermissionsOnCategory
     */
    public function __construct(
        Data $_helperData,
        StoreManagerInterface $storeManager,
        PermissionsData $permissionsData,
        Session $customerSession,
        FlashSales $flashSalesModel,
        ApplyPermissionsOnCategory $applyPermissionsOnCategory
    ) {
        $this->_permissionsData = $permissionsData;
        $this->_customerSession = $customerSession;
        $this->_flashSalesModel = $flashSalesModel;
        $this->_helperData = $_helperData;
        $this->_storeManager = $storeManager;
        $this->applyPermissionsOnCategory = $applyPermissionsOnCategory;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $category = $observer->getEvent()->getCategory();

        $permissions = $this->_flashSalesModel->getIndexForCategory(
            $category->getId(),
            $this->_storeManager->getStore()->getId()
        );

        if ($permissions) {
            foreach ($permissions as $permission) {
                $category->setPermissions($permission);
            }
        }

        $this->applyPermissionsOnCategory->execute($category);
        if ($observer->getEvent()->getCategory()->getIsHidden()) {
            $observer->getEvent()->getControllerAction()->getResponse()->setRedirect(
                $this->_permissionsData->getLandingPageUrl($category)
            );

            throw new LocalizedException(
                __('You may need more permissions to access this category.')
            );
        }

        return $this;
    }
}
