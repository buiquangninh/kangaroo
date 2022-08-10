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

namespace Lof\FlashSales\Model\FlashSales;

use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection;
use Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;
use Lof\FlashSales\Model\DateResolver;

class DataProvider extends AbstractDataProvider
{

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DateResolver
     */
    protected $dateResolver;

    /**
     * @var Image
     */
    protected $flashSaleImage;

    /**
     * @var FileInfo
     */
    private $_fileInfo;

    /**
     * @var
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param RequestInterface $request
     * @param DateResolver $dateResolver
     * @param array $meta
     * @param array $data
     * @param FileInfo|null $fileInfo
     * @param Image|null $flashSaleImage
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        DateResolver $dateResolver,
        array $meta = [],
        array $data = [],
        FileInfo $fileInfo = null,
        ?Image $flashSaleImage = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_fileInfo = $fileInfo ?: ObjectManager::getInstance()->get(FileInfo::class);
        $this->flashSaleImage = $flashSaleImage ?? ObjectManager::getInstance()->get(Image::class);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->dateResolver = $dateResolver;
    }

    /**
     * Get data
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadDataAfterSave($model);
        }
        $data = $this->dataPersistor->get('lof_flashsales_events');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadDataAfterSave($model);
            $this->dataPersistor->clear('lof_flashsales_events');
        }
        return $this->loadedData;
    }

    /**
     * @param $model
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function loadDataAfterSave($model)
    {
        $flashSalesData = $model->getData();
        $id = $model->getId();
        $this->loadedData[$id]['general'] = $model->getData();
        $this->loadedData[$id]['general']['noteAppliedProduct'] = __('Products with smaller position will show first');
        $this->loadedData[$id]['content']['description'] = $model->getData('description');
        $this->loadedData[$id]['general']['date_group'] = [
            'from_date' => $model->getFromDate(),
            'to_date' => $model->getToDate()
        ];
        $this->loadedData[$id]['general']['product_conditons']
        ['assign_category']['category_id'] = $model->getCategoryId();
        $this->loadedData[$id]['general']['product_conditons']
        ['is_assign_category'] = $model->getIsAssignCategory();
        $this->loadedData[$id]['private_sale_permissions'] = [
            'is_private_sale' => $model->getIsPrivateSale(),
            'is_default_private_config' => $model->getIsDefaultPrivateConfig(),
            'restricted_landing_page' => $model->getRestrictedLandingPage(),
            'grant_event_view' => $model->getGrantEventView(),
            'grant_event_product_price' => $model->getGrantEventProductPrice(),
            'grant_checkout_items' => $model->getGrantCheckoutItems(),
            'grant_event_view_groups' => $model->getGrantEventViewGroups() ? explode(',', $model->getGrantEventViewGroups()) : null,
            'grant_event_product_price_groups' => $model->getGrantEventProductPriceGroups() ? explode(',', $model->getGrantEventProductPriceGroups()) : null,
            'grant_checkout_items_groups' => $model->getGrantCheckoutItemsGroups() ? explode(',', $model->getGrantCheckoutItemsGroups()) : null,
            'display_cart_mode' => $model->getDisplayCartMode(),
            'display_product_mode' => $model->getDisplayProductMode(),
            'cart_button_title' => $model->getCartButtonTitle(),
            'message_hidden_add_to_cart' => $model->getMessageHiddenAddToCart(),
        ];

        $this->loadedData[$id]['content']['thumbnail_image'] = $this->convertValues(
            $model,
            $flashSalesData,
            'thumbnail_image'
        );

        $this->loadedData[$id]['content']['header_banner_image'] = $this->convertValues(
            $model,
            $flashSalesData,
            'header_banner_image'
        );

        $this->loadedData[$id]['rule']['conditions'] = $model->getConditons();
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        $id = $this->request->getParam('id');
        $bool = isset($id);
        $meta['general']['children']['status']['arguments']['data']['config']['visible'] = $bool;
        return $meta;
    }

    /**
     * @param $model
     * @param $flashSalesData
     * @param $field
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function convertValues($model, $flashSalesData, $field): array
    {
        if ($fileName = $model->getData($field)) {
            if ($this->_fileInfo->isExist($fileName)) {
                $stat = $this->_fileInfo->getStat($fileName);
                $mime = $this->_fileInfo->getMimeType($fileName);
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $flashSalesData['content'][$field][0]['name'] = basename($fileName);
                $flashSalesData['content'][$field][0]['url'] = $this->flashSaleImage->getUrl($model, $field);
                $flashSalesData['content'][$field][0]['size'] = isset($stat['size']) ? $stat['size'] : 0;
                $flashSalesData['content'][$field][0]['type'] = $mime;
            }

            return $flashSalesData['content'][$field];
        }
        return $flashSalesData['content'][$field] = [];
    }
}
