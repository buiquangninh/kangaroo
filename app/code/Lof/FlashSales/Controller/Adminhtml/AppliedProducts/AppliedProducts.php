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

namespace Lof\FlashSales\Controller\Adminhtml\AppliedProducts;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\AppliedProductsRepository;
use Lof\FlashSales\Model\Indexer\ProductPriceIndexer;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Setup\Console\InputValidationException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Ui\Component\MassAction\Filter;
use Lof\FlashSales\Model\AppliedProducts as AppliedProductsModel;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class AppliedProducts
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AppliedProducts extends Action
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AppliedProductsRepository
     */
    protected $appliedProductsRepository;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var AppliedProductsModel
     */
    protected $appliedProducts;

    /**
     * @var ProductPriceIndexer
     */
    protected $_productPriceIndexer;

    /**
     * Constructor
     *
     * @param Context $context
     * @param AppliedProductsModel $appliedProducts
     * @param Data $helperData
     * @param Filter $filter
     * @param AppliedProductsRepository $appliedProductsRepository
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param ProductPriceIndexer $productPriceIndexer
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        AppliedProductsModel $appliedProducts,
        Data $helperData,
        Filter $filter,
        AppliedProductsRepository $appliedProductsRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        ProductPriceIndexer $productPriceIndexer,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->_productPriceIndexer = $productPriceIndexer;
        $this->appliedProducts = $appliedProducts;
        $this->filter = $filter;
        $this->helperData = $helperData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->appliedProductsRepository = $appliedProductsRepository;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $error = false;
        try {
            $postData = $this->getRequest()->getPost();
            $message = $this->processAndSaveData($postData);
        } catch (NoSuchEntityException $e) {
            $error = true;
            $message = $e->getMessage();
        } catch (CouldNotSaveException $e) {
            $error = true;
            $message = $e->getMessage();
        } catch (InputValidationException $e) {
            $error = true;
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $error = true;
            $this->logger->critical($e);
            $message = __("Something went wrong! Please check the log.");
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'message' => $message,
            'error' => $error
        ]);

        return $resultJson;
    }

    /**
     * @param $postData
     * @return mixed
     */
    public function processAndSaveData($postData)
    {
        return $postData;
    }
}
