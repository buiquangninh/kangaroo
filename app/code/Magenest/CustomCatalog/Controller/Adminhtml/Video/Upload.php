<?php
/**
 * Copyright © 2021 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_CustomCatalog extension
 * NOTICE OF LICENSE
 *
 * @author   PhongNguyen
 * @category Magenest
 * @package  Magenest_CustomCatalog
 */

namespace Magenest\CustomCatalog\Controller\Adminhtml\Video;

use Magento\Catalog\Model\ImageUploader;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Upload
 *
 * @package Magenest\CustomCatalog\Controller\Adminhtml\Video
 */
class Upload extends \Magento\Backend\App\Action
{
    const PATH_TMP_CATALOG_PRODUCT_VIDEO = 'catalog/tmp/product/video/';
    const PATH_CATALOG_PRODUCT_VIDEO     = 'catalog/product/video/';

    /**
     * @var ImageUploader
     */
    protected $_imageUploader;

    /**
     * Upload constructor.
     *
     * @param Context       $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        ImageUploader $imageUploader
    ) {
        $this->_imageUploader = $imageUploader;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result            = $this->_imageUploader->saveFileToTmpDir('file');
            $result['cookie']  = [
                'name'     => $this->_getSession()->getName(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain(),
            ];
            $result['success'] = true;
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
