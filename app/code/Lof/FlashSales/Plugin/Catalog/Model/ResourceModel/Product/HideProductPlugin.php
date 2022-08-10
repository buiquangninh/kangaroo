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

namespace Lof\FlashSales\Plugin\Catalog\Model\ResourceModel\Product;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\Adminhtml\System\Config\Source\ConfigData;
use Lof\FlashSales\Model\FlashSales;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

class HideProductPlugin
{

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Data
     */
    protected $helperData;

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
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * HideProductPlugin constructor.
     * @param UserContextInterface $userContext
     * @param Data $helperData
     * @param FlashSales $flashSalesModel
     * @param Session $customerSession
     * @param ProductFactory $productFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UserContextInterface $userContext,
        Data $helperData,
        FlashSales $flashSalesModel,
        Session $customerSession,
        ProductFactory $productFactory,
        ProductCollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_productFactory = $productFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_flashSalesModel = $flashSalesModel;
        $this->helperData = $helperData;
        $this->userContext = $userContext;
    }

    /**
     * @param ProductCollection $collection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array|bool[]
     */
    public function beforeLoad(
        ProductCollection $collection,
        $printQuery = false,
        $logQuery = false
    ): array {
        if (!$collection->isLoaded()) {
            $this->addPrivateProductFilter($collection);
        }

        return [$printQuery, $logQuery];
    }

    /**
     * @param ProductCollection $collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function addPrivateProductFilter(ProductCollection $collection)
    {
        $customerGroupId = $this->_customerSession->getCustomerGroupId();
        $productAllIds = $this->_productCollectionFactory->create()->getAllIds();
        $this->helperData->getCatalogCategoryViewGroups();
        $this->helperData->getCatalogCategoryViewMode();

        if (!$this->helperData->isEnabled()) {
            return;
        }

        // avoid adding shared catalog filter on create/edit products by api
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            || $this->userContext->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION) {
            return;
        }
        $hideProductIds = [];
        if ($productPermissions = $this->getProductPrivate($productAllIds)) {
            foreach ($productPermissions->getData() as $productPermission) {
                if (!!$productPermission['is_default_private_config']) {
                    $viewMode = $this->helperData->getCatalogCategoryViewMode();
                    $groups = $this->helperData->getCatalogCategoryViewGroups();
                    $displayProductMode = $this->helperData->getDisplayProductMode();
                } else {
                    $viewMode = $productPermission['grant_event_view'];
                    $groups = explode(',', $productPermission['grant_event_view_groups']);
                    $displayProductMode = $productPermission['display_product_mode'];
                }

                if ($viewMode == ConfigData::GRANT_CUSTOMER_GROUP ||
                    $viewMode == ConfigData::GRANT_NONE) {
                    if (!in_array($customerGroupId, $groups)) {
                        if (!!$displayProductMode) {
                            $hideProductIds[] = $productPermission['product_id'];
                        }
                    }

                    if ($viewMode == ConfigData::GRANT_NONE) {
                        if (!!$displayProductMode) {
                            $hideProductIds[] = $productPermission['product_id'];
                        }
                    }
                }
            }

            if ($hideProductIds) {
                $accessProductIds = [];
                foreach ($productAllIds as $productId) {
                    if (!in_array($productId, $hideProductIds)) {
                        $accessProductIds[] = $productId;
                    }
                }

                $collection->addAttributeToFilter('entity_id', ['in' => $accessProductIds]);
            }
        }

        $collection->setFlag('has_private_product_filter', true);
    }

    /**
     * @param $productAllIds
     * @return \Magento\Framework\DataObject
     * @throws NoSuchEntityException
     */
    public function getProductPrivate($productAllIds)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $productPermissions = new \Magento\Framework\DataObject();
        $this->_flashSalesModel->getIndexProductPrivate($productPermissions, $productAllIds, $storeId);

        return $productPermissions;
    }
}
