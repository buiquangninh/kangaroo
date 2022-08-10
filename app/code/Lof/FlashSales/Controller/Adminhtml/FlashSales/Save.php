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

namespace Lof\FlashSales\Controller\Adminhtml\FlashSales;

use Lof\FlashSales\Controller\Adminhtml\FlashSales;
use Magento\Bundle\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ResourceConfigurable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends FlashSales implements HttpPostActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Lof_FlashSales::flashsales_save';

    /**
     * Save action
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('id');

            $model = $this->_objectManager->create(\Lof\FlashSales\Model\FlashSales::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This flash sale no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $postData = $this->prepareData($data);
            $oldCategoryId = $this->getOldCategoryId($model, $postData);
            if (!isset($postData['category_id'])) {
                $postData['category_id'] = null;
            }
            $dateResolver = $this->_objectManager->get(\Lof\FlashSales\Model\DateResolver::class);
            $data = new \Magento\Framework\DataObject($postData);
            $model->loadPost($data->getData());
            $model->setFromDate(
                $dateResolver->convertDate($data->getFromDate(), true)
            )->setToDate(
                $dateResolver->convertDate($data->getToDate(), true)
            )->applyStatusByDates();
            $validateResult = $model->validateTime();
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->dataPersistor->set('lof_flashsales_events', $postData);
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
            }
            try {
                if (!!$model->getIsAssignCategory()) {
                    if ($this->validateCategoryAdded($model)) {
                        foreach ($this->validateCategoryAdded($model) as $errorMessage) {
                            $this->messageManager->addErrorMessage($errorMessage);
                        }
                        $this->dataPersistor->set('lof_flashsales_events', $postData);
                        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                    }
                }

                //  if (empty($model->getConditions()['conditions'])) {
                //  $this->messageManager->addErrorMessage(__('You must choose Product Conditions.'));
                //  $this->dataPersistor->set('lof_flashsales_events', $postData);
                //  return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                //  }

                $model->save();
                $this->saveAppliedProducts($model);
                if ($model->getIsAssignCategory()) {
                    $this->appliedCategories($model, $oldCategoryId);
                }

                if ($model->getIsActive() == 0) {
                    $this->helperData->reindexProductPrice();
                }

                $this->reindexProductApplied($model);

                $this->messageManager->addSuccessMessage(__('You saved the Flash Sale Campaign.'));
                $this->dataPersistor->clear('lof_flashsales_events');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Flash Sale Campaign.')
                );
            }

            $this->dataPersistor->set('lof_flashsales_events', $postData);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $model
     * @throws LocalizedException
     */
    public function reindexProductApplied($model)
    {
        if ($model->getIsActive()) {
            if (!$this->_productPriceIndexer->isIndexerScheduled()) {
                $this->helperData->reindexProductPrice();
                $this->_productPriceIndexer->executeByFlashSalesId($model->getFlashsalesId());
            } else {
                $this->_productPriceIndexer->getIndexer()->invalidate();
            }
        }
    }

    /**
     * @return true|array
     * @throws LocalizedException
     */
    public function validateCategoryAdded($model)
    {
        $categoryDataAdded = [];
        $currentCategoryId = $model->getCategoryId();
        $flashSales = $this->flashSalesCollectionFactory->create()->getItems();
        foreach ($flashSales as $categoryAdded) {
            if ($model->getFlashSalesId() != $categoryAdded->getFlashsalesId()) {
                $categoryDataAdded[] = $categoryAdded->getCategoryId();
            }
        }
        if (in_array($currentCategoryId, $categoryDataAdded)) {
            $categoryName = $this->categoryRepository->get($currentCategoryId)->getName();

            return [__('Another event has been assigned with category %1', $categoryName)];
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function prepareData($data)
    {
        if (isset($data['general']) && is_array($data['general'])) {
            if (isset($data['content']) && is_array($data['content'])) {
                $data['general']['content'] = $data['content'];
            }
            if (isset($data['private_sale_permissions'])) {
                $data['general']['private_sale_permissions']
                    = $data['private_sale_permissions'];
            }
            if (isset($data['rule'])) {
                $data['general']['rule'] = $data['rule'];
            }

            $data = $data['general'];

            if (isset($data['date_group']['from_date'])
                && isset($data['date_group']['to_date'])
            ) {
                $data['from_date'] = $data['date_group']['from_date'];
                $data['to_date'] = $data['date_group']['to_date'];
                unset($data['date_group']);
            }
            unset($data['general']);
        }

        if (isset($data['content']) && is_array($data['content'])) {
            if (isset($data['content']['thumbnail_image'])
                && is_array($data['content']['thumbnail_image'])
            ) {
                if (isset($data['content']['thumbnail_image'][0]['name'])
                ) {
                    $data['thumbnail_image']
                        = $data['content']['thumbnail_image'][0]['name'];
                } else {
                    unset($data['content']['thumbnail_image']);
                }
            } else {
                $data['thumbnail_image'] = null;
            }

            if (isset($data['content']['header_banner_image'])
                && is_array($data['content']['header_banner_image'])
            ) {
                if (isset($data['content']['header_banner_image'][0]['name'])
                ) {
                    $data['header_banner_image']
                        = $data['content']['header_banner_image'][0]['name'];
                } else {
                    unset($data['content']['header_banner_image']);
                }
            } else {
                $data['header_banner_image'] = null;
            }

            if (isset($data['content']['description'])) {
                $data['description'] = $data['content']['description'];
            }
            unset($data['content']);
        }

        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
        }

        if (isset($data['product_conditons']['is_assign_category'])) {
            $data['is_assign_category'] = $data['product_conditons']['is_assign_category'];
            unset($data['product_conditons']['is_assign_category']);
        }

        if (isset($data['product_conditons']['assign_category']['category_id'])) {
            $data['category_id'] = $data['product_conditons']['assign_category']['category_id'];
            unset($data['product_conditons']['assign_category']);
        }

        if (isset($data['private_sale_permissions'])) {
            $data['is_private_sale'] = $data['private_sale_permissions']['is_private_sale'];
            $data['is_default_private_config'] = $data['private_sale_permissions']['is_default_private_config'];
            $data['grant_event_view'] = $data['private_sale_permissions']['grant_event_view'];
            $data['grant_event_product_price'] = $data['private_sale_permissions']['grant_event_product_price'];
            $data['grant_checkout_items'] = $data['private_sale_permissions']['grant_checkout_items'];

            if (isset($data['private_sale_permissions']['restricted_landing_page'])) {
                $data['restricted_landing_page'] = $data['private_sale_permissions']['restricted_landing_page'];
            }

            if (isset($data['private_sale_permissions']['grant_event_view_groups'])
                && is_array($data['private_sale_permissions']['grant_event_view_groups'])) {
                $data['grant_event_view_groups'] = implode(",", $data['private_sale_permissions']
                ['grant_event_view_groups']);
            }

            if (isset($data['private_sale_permissions']['grant_event_product_price_groups'])
                && is_array($data['private_sale_permissions']['grant_event_product_price_groups'])) {
                $data['grant_event_product_price_groups'] = implode(",", $data['private_sale_permissions']
                ['grant_event_product_price_groups']);
            }

            if (isset($data['private_sale_permissions']['grant_checkout_items_groups'])
                && is_array($data['private_sale_permissions']['grant_checkout_items_groups'])) {
                $data['grant_checkout_items_groups'] = implode(",", $data['private_sale_permissions']
                ['grant_checkout_items_groups']);
            }

            if (isset($data['private_sale_permissions']['display_product_mode'])) {
                $data['display_product_mode'] = $data['private_sale_permissions']['display_product_mode'];
            }

            if (isset($data['private_sale_permissions']['display_cart_mode'])) {
                $data['display_cart_mode'] = $data['private_sale_permissions']['display_cart_mode'];
            }

            if (isset($data['private_sale_permissions']['cart_button_title'])) {
                $data['cart_button_title'] = $data['private_sale_permissions']['cart_button_title'];
            }

            if (isset($data['private_sale_permissions']['message_hidden_add_to_cart'])) {
                $data['message_hidden_add_to_cart'] = $data['private_sale_permissions']['message_hidden_add_to_cart'];
            }

            if ($data['is_private_sale'] == 0) {
                $data['is_default_private_config'] = 1;
            }

            if (!!$data['is_default_private_config']) {
                $data['grant_event_view'] = 1;
                $data['grant_event_product_price'] = 1;
                $data['grant_checkout_items'] = 1;
                $data['display_product_mode'] = 1;
                $data['display_cart_mode'] = 1;
            }

            unset($data['private_sale_permissions']);
        }

        return $data;
    }

    /**
     * @param \Lof\FlashSales\Model\FlashSales $flashSale
     * @throws CouldNotSaveException
     */
    protected function saveAppliedProducts($flashSale)
    {
        try {
            $newProductsData = $flashSale->getMatchingProductIds(['name', 'sku', 'price', 'type_id']);
            $currentProductsData = $this->getAppliedProducts($flashSale);
            $noLongerApplyProductIds = $this->getNoLongerApplyProductIds(
                $newProductsData,
                $currentProductsData
            );

            if ($noLongerApplyProductIds) {
                $appliedProductIds = [];
                foreach ($noLongerApplyProductIds as $entityId) {
                    $appliedProductIds[] = $entityId;
                    $appliedProduct = $this->appliedProductsFactory->create()->load($entityId);
                    $this->appliedProductsResource->delete($appliedProduct);
                    if ($flashSale->getIsAssignCategory()) {
                        $this->categoryLinkRepository->deleteBySkus(
                            $flashSale->getCategoryId(),
                            [$appliedProduct->getSku()]
                        );
                    }
                }
            }

            $newApplyProducts = $this->getNewApplyProductPrice(
                $newProductsData,
                $currentProductsData
            );
            $importAppliedProducts = [];

            foreach ($newProductsData as $productId => $data) {
                if (in_array($productId, $newApplyProducts)) {
                    $importAppliedProducts[] = [
                        'name' => $data["name"] ?? null,
                        'type_id' => $data["type_id"] ?? null,
                        'flashsales_id' => $flashSale->getFlashSalesId(),
                        'original_price' => $data["price"] ?? null,
                        'product_id' => $productId,
                        'discount_amount' => $flashSale->getDiscountAmount(),
                        'qty_limit' => 0,
                        'sku' => $data["sku"] ?? null,
                        'position' => 0
                    ];
                }
            }

            $connection = $this->appliedProductsResource->getConnection();
            $table = $this->appliedProductsResource->getMainTable();
            if (!empty($importAppliedProducts)) {
                $connection->insertMultiple($table, $importAppliedProducts);
            }

            $dataPosition = $this->getRequest()->getPostValue('position');

            // Update data position for applied product
            if ($dataPosition) {
                $appliedProducts = $this->getAppliedProducts($flashSale);
                $dataNewAppliedProduct = [];

                foreach ($appliedProducts as $appliedProduct) {
                    $entityId = $appliedProduct->getEntityId();
                    $positionValue = $appliedProduct->getPosition();
                    if (isset($dataPosition[$entityId]) && is_numeric($dataPosition[$entityId])) {
                        $positionValue = $dataPosition[$entityId];
                    }
                    $dataNewAppliedProduct[] = [
                        'entity_id' => $entityId,
                        'position' => $positionValue
                    ];
                }

                $connection->insertOnDuplicate(
                    $table,
                    $dataNewAppliedProduct
                );
            }
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __("Something went wrong while saving the flash sale.")
            );
        }
    }

    /**
     * @param $model
     * @param $data
     * @return String|null
     */
    public function getOldCategoryId($model, $data)
    {
        if (isset($data['flashsales_id']) && isset($data['category_id'])) {
            $currentCategoryId = $model->load($data['flashsales_id'])->getCategoryId();
            $newCategoryId = $data['category_id'];
            if ($currentCategoryId != $newCategoryId) {
                return $currentCategoryId;
            }
        }

        return null;
    }

    /**
     * @param $flashSale
     * @param $oldCategoryId
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function appliedCategories($flashSale, $oldCategoryId)
    {
        $newProductInCategory = [];
        $products = [];
        $productSkuList = [];
        $newCategoryId = $flashSale->getCategoryId();
        $category = $this->categoryRepository->get($newCategoryId);
        $currentProductInCategory = $category->getProductCollection()->getAllIds();
        $appliedProducts = $this->getAppliedProductsHasPrice($flashSale);
        foreach ($appliedProducts as $product) {
            $productSkuList[] = $product->getSku();
            if ($product->getFlashSalePrice() && $product->getFlashSalePrice() >= 0) {
                $newProductInCategory[] = $product->getProductId();
            }

            $confProduct = $this->_objectManager
                ->create(ResourceConfigurable::class)
                ->getParentIdsByChild($product->getProductId());

            if (isset($confProduct[0])) {
                $newProductInCategory[] = $confProduct[0];
            }

            // TODO: can remove
            if (Configurable::TYPE_CODE === $product->getTypeId()
                || Grouped::TYPE_CODE === $product->getTypeId()
                || Type::TYPE_CODE === $product->getTypeId()) {
                $newProductInCategory[] = $product->getProductId();
            }
        }
        if ($oldCategoryId) {
            $this->categoryLinkRepository->deleteBySkus(
                $oldCategoryId,
                $productSkuList
            );
        }
        if ($newProductInCategory && $currentProductInCategory) {
            $products = $this->getProductPositions(array_merge($currentProductInCategory, $newProductInCategory));
        } elseif (!$newProductInCategory) {
            $products = $this->getProductPositions($currentProductInCategory);
        } elseif (!$currentProductInCategory) {
            $products = $this->getProductPositions($newProductInCategory);
        }

        $category->setPostedProducts($products);
        $category->save();
    }

    /**
     * @param $products
     * @return array
     */
    protected function getProductPositions($products)
    {
        $addProductsToCategory = [];
        foreach ($products as $productId) {
            $addProductsToCategory[$productId] = 0;
        }
        return $addProductsToCategory;
    }

    /**
     * @param $newData
     * @param $oldData
     * @return array
     */
    protected function getNoLongerApplyProductIds($newData, $oldData)
    {
        $newAppliedProductIds = $this->getAppliedProductIds($newData);
        $currentAppliedProductIds = $this->getAppliedProductIds(
            $oldData,
            'old'
        );

        $removeProductIds = array_diff(
            array_keys($currentAppliedProductIds),
            $newAppliedProductIds
        );

        $noLongerApplyProductIds = [];
        foreach ($currentAppliedProductIds as $productId => $productPriceId) {
            if (in_array($productId, $removeProductIds)) {
                $noLongerApplyProductIds[] = $productPriceId;
            }
        }

        return $noLongerApplyProductIds;
    }

    /**
     * @param $appliedProducts
     * @param string $dataType
     * @return array
     */
    private function getAppliedProductIds($appliedProducts, $dataType = "new")
    {
        $data = [];
        if ($appliedProducts && $dataType == "old") {
            foreach ($appliedProducts as $appliedProduct) {
                $data[(int)$appliedProduct->getProductId()] = (int)$appliedProduct->getEntityId();
            }
        } else {
            $data = array_keys($appliedProducts);
        }

        return $data;
    }

    /**
     * @param $newData
     * @param $oldData
     * @return array
     */
    protected function getNewApplyProductPrice($newData, $oldData)
    {
        $newProductPriceIds = $this->getAppliedProductIds($newData);
        $currentProductPriceIds = $this->getAppliedProductIds($oldData, "old");

        return array_diff(
            $newProductPriceIds,
            array_keys($currentProductPriceIds)
        );
    }

    /**
     * @param $model
     * @return \Magento\Framework\DataObject[]
     */
    public function getAppliedProductsHasPrice($model)
    {
        return $this->getAppliedProductsCollection()
            ->addFieldToFilter('flashsales_id', $model->getFlashSalesId())
            ->addFieldToFilter('flash_sale_price', ['gt' => 0])
            ->getItems();
    }

    /**
     * @param $model
     * @return \Magento\Framework\DataObject[]
     */
    public function getAppliedProducts($model)
    {
        return $this->getAppliedProductsCollection()
            ->addFieldToFilter('flashsales_id', $model->getFlashSalesId())
            ->getItems();
    }

    /**
     * @return \Lof\FlashSales\Model\ResourceModel\AppliedProducts\Collection
     */
    public function getAppliedProductsCollection()
    {
        return $this->appliedProductsCollectionFactory->create();
    }
}
