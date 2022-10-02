<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Controller\Proxy;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magetop\Smtp\Helper\Data;
use Magetop\Smtp\Helper\EmailMarketing;

/**
 * Class Index
 * @package Magetop\Smtp\Controller\Proxy
 */
class Index extends Action
{
    /**
     * @var EmailMarketing
     */
    protected $helperEmailMarketing;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param EmailMarketing $helperEmailMarketing
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        EmailMarketing $helperEmailMarketing,
        RawFactory $resultRawFactory
    ) {
        $this->helperEmailMarketing = $helperEmailMarketing;
        $this->resultRawFactory     = $resultRawFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $url    = EmailMarketing::PROXY_URL;
            if (isset($params['path'])) {
                $url = EmailMarketing::PROXY_URL . $params['path'];
            }

            $response = $this->helperEmailMarketing->sendRequestProxy($url, $params);
            if (isset($params['type'])) {
                $result = $this->resultRawFactory->create();
                $result->setHeader('content-type', $params['type']);
                $result->setContents($response);

                return $result;
            }
        } catch (Exception $e) {
            $response = [];
        }

        return $this->getResponse()->representJson(Data::jsonEncode($response));
    }
}
