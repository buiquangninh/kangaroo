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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

class ApplyCategoryPermissionOnLoadCollectionObserver implements ObserverInterface
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
     * ApplyCategoryPermissionOnLoadCollectionObserver constructor.
     * @param Data $_helperData
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param FlashSales $flashSalesModel
     * @param ApplyPermissionsOnCategory $applyPermissionsOnCategory
     */
    public function __construct(
        Data $_helperData,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        FlashSales $flashSalesModel,
        ApplyPermissionsOnCategory $applyPermissionsOnCategory
    ) {
        $this->_customerSession = $customerSession;
        $this->_flashSalesModel = $flashSalesModel;
        $this->_helperData = $_helperData;
        $this->_storeManager = $storeManager;
        $this->applyPermissionsOnCategory = $applyPermissionsOnCategory;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $permissions = [];
        $categoryCollection = $observer->getEvent()->getCategoryCollection();
        $categoryIds = $categoryCollection->getColumnValues('entity_id');

        if ($categoryIds) {
            $permissions = $this->_flashSalesModel->getIndexForCategory(
                $categoryIds,
                $this->_storeManager->getStore()->getId()
            );
        }

        foreach ($permissions as $permission) {
            $categoryCollection->getItemById($permission['category_id'])->setPermissions($permission);
        }

        foreach ($categoryCollection as $category) {
            $this->applyPermissionsOnCategory->execute($category);
        }

        return $this;
    }
}
