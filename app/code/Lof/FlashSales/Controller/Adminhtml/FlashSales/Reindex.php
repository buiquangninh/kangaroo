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

use Lof\FlashSales\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Exception;
use Psr\Log\LoggerInterface;

class Reindex extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Lof_FlashSales::flashsales_update';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * Reindex constructor.
     * @param Context $context
     * @param Data $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Data $helperData,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws \Throwable
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->helperData->reindexProductPrice();
                $this->messageManager->addSuccessMessage(__('The Flash Sale has been re-indexed.'));
                $this->_redirect('*/*/edit', ['id' => $id]);
                return;
            } catch (LocalizedException $e) {
                $this->_redirect('*/*/edit', ['id' => $id]);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->logger->critical($e);
                $this->_redirect('*/*/edit', ['id' => $id]);
                $this->messageManager->addExceptionMessage($e, __('There was a problem with reindexing process.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
    }
}
