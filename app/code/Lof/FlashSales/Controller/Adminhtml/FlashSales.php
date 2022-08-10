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

namespace Lof\FlashSales\Controller\Adminhtml;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\AppliedProductsFactory;
use Lof\FlashSales\Model\Indexer\ProductPriceIndexer;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory as AppliedProductsCollectionFactory;
use Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory as FlashSalesCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

abstract class FlashSales extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Lof_FlashSales::flashsales';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var AppliedProductsCollectionFactory
     */
    protected $appliedProductsCollectionFactory;

    /**
     * @var FlashSalesCollectionFactory
     */
    protected $flashSalesCollectionFactory;

    /**
     * @var AppliedProducts
     */
    protected $appliedProductsResource;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AppliedProductsFactory
     */
    protected $appliedProductsFactory;

    /**
     * @var CategoryLinkRepository
     */
    protected $categoryLinkRepository;

    /**
     * @var  CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductPriceIndexer
     */
    protected $_productPriceIndexer;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param Data $helperData
     * @param FlashSalesCollectionFactory $flashSalesCollectionFactory
     * @param AppliedProductsCollectionFactory $appliedProductsCollectionFactory
     * @param AppliedProductsFactory $appliedProductsFactory
     * @param AppliedProducts $appliedProductsResource
     * @param LoggerInterface $logger
     * @param ProductPriceIndexer $productPriceIndexer
     * @param CategoryLinkRepository $categoryLinkRepository
     * @param CategoryRepository $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        Data $helperData,
        FlashSalesCollectionFactory $flashSalesCollectionFactory,
        AppliedProductsCollectionFactory $appliedProductsCollectionFactory,
        AppliedProductsFactory $appliedProductsFactory,
        AppliedProducts $appliedProductsResource,
        LoggerInterface $logger,
        ProductPriceIndexer $productPriceIndexer,
        CategoryLinkRepository $categoryLinkRepository,
        CategoryRepository $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->appliedProductsFactory = $appliedProductsFactory;
        $this->categoryRepository = $categoryRepository;
        $this->_productPriceIndexer = $productPriceIndexer;
        $this->logger = $logger;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->appliedProductsResource = $appliedProductsResource;
        $this->appliedProductsCollectionFactory = $appliedProductsCollectionFactory;
        $this->helperData = $helperData;
        $this->flashSalesCollectionFactory = $flashSalesCollectionFactory;
        $this->dataPersistor = $dataPersistor;
    }
}
