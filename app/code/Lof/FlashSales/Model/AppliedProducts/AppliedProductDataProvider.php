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

namespace Lof\FlashSales\Model\AppliedProducts;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\FlashSalesRepository;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\Collection;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

class AppliedProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FlashSalesRepository
     */
    protected $flashSalesRepository;

    protected $loadedData;

    /**
     * AppliedProductDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param SerializerInterface $serializer
     * @param FlashSalesRepository $flashSalesRepository
     * @param Data $helperData
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        SerializerInterface $serializer,
        FlashSalesRepository $flashSalesRepository,
        Data $helperData,
        array $meta = [],
        array $data = []
    ) {
        $this->flashSalesRepository = $flashSalesRepository;
        $this->helperData = $helperData;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $id = $model->getEntityId();
            $this->loadedData[$id] = $model->getData();
        }
        $data = $this->dataPersistor->get('lof_flashsales_appliedproducts');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getEntityId()] = $model->getData();
            $this->dataPersistor->clear('lof_flashsales_appliedproducts');
        }

        return $this->loadedData;
    }
}
