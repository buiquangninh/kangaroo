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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryRepository;

class TimLineActions extends Column
{

    /**
     * Url path
     */
    const URL_PATH_EDIT = 'lof_flashsales/flashsales/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CategoryRepository
     */
    protected $_categoryRepository;

    /**
     * TimLineActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepository $categoryRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        CategoryRepository $categoryRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_categoryRepository = $categoryRepository;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['flashsales_id'])) {
                    $isAssignCategory = $item['is_assign_category'];
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'id' => $item['flashsales_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'view' => [
                            'href' => $this->getViewUrl($item),
                            'label' => __('View'),
                            'target' => '_blank',
                            'hidden' => !!$isAssignCategory ? false : true
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $item
     * @return string
     */
    public function getViewUrl($item)
    {
        return $this->getCategoryUrl($item);
    }

    /**
     * @param $item
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategoryUrl($item)
    {
        if (!isset($item['category_id']) || !$item['category_id']) {
            return '#';
        }

        $categoryId = (int) $item['category_id'];
        $category = $this->_categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());

        if ($category) {
            return $category->getUrl();
        }

        return '#';
    }
}
