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

namespace Lof\FlashSales\Ui\Component\Listing\Column;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param Repository $assetRepo
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        Repository $assetRepo,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_assetRepo = $assetRepo;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $path = $this->storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . 'lof_flashsales/tmp/flashsales/';
            foreach ($dataSource['data']['items'] as & $item) {
                $flashsales = new DataObject($item);
                if (!$item['thumbnail_image']) {
                    $item['thumbnail_image'] = $this->_assetRepo->getUrl(
                        'Lof_FlashSales::images/placeholder/thumbnail.jpg'
                    );
                    $item[$fieldName . '_src'] = $item['thumbnail_image'];
                    $item[$fieldName . '_orig_src'] = $item['thumbnail_image'];
                } else {
                    $item[$fieldName . '_src'] = $path . $item['thumbnail_image'];
                    $item[$fieldName . '_orig_src'] = $path . $item['thumbnail_image'];
                }
                $item[$fieldName . '_alt'] = $item['event_name'];
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'lof_flashsales/flashsales/edit',
                    [
                        'id' => $flashsales->getData('flashsales_id'),
                        'store' => $this->context->getRequestParam('store')
                    ]
                );
            }
        }

        return $dataSource;
    }
}
