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

namespace Lof\FlashSales\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageCache\Model\Config;
use Psr\Log\LoggerInterface;

class AddCountDownOptionsHtml extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Http
     */
    protected $http;

    /**
     * @var Config
     */
    protected $config;

    /**
     * SwatchOptions constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Config $config
     * @param Http $http
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Json $json,
        LoggerInterface $logger,
        Config $config,
        Http $http
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->http = $http;
        $this->config = $config;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        /** @var \Magento\Framework\App\ResponseInterface $response */
        $response = $this->getResponse();
        $response->setPublicHeaders($this->config->getTtl());
        $resultJson->setData(['output' => []]);
        return $resultJson;
    }
}
