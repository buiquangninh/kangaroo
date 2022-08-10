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

use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Downloadable\Model\Product\Type as DownloadablePType;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;

class AppliedProductActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    const URL_PATH_DELETE_DISCOUNT_AMOUNT = 'lof_flashsales/appliedproducts/deleteDiscountAmount';
    const URL_PATH_DELETE_QUANTIY_LIMIT = 'lof_flashsales/appliedproducts/deleteQtyLimit';

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
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
                $title = $this->getEscaper()->escapeHtmlAttr($item['name']);
                $disable = $this->checkProductTypeDisplay($item['type_id']);
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'callback' => [
                            [
                                'provider' => 'lof_flashsales_form.areas.applied_products.applied_products'
                                    . '.lof_applied_products_update_modal.update_lof_appliedproducts_form_loader',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'lof_flashsales_form.areas.applied_products.applied_products'
                                    . '.lof_applied_products_update_modal',
                                'target' => 'openModal',
                            ],
                            [
                                'provider' => 'lof_flashsales_form.areas.applied_products.applied_products'
                                    . '.lof_applied_products_update_modal.update_lof_appliedproducts_form_loader',
                                'target' => 'render',
                                'params' => [
                                    'id' => $item['entity_id'],
                                ],
                            ]
                        ],
                        'href' => '#',
                        'label' => __('Edit'),
                        'hidden' => $disable,
                    ];

                    $item[$name]['delete_discount_amount'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_DELETE_DISCOUNT_AMOUNT,
                            ['id' => $item['entity_id']]
                        ),
                        'label' => __('Delete Discount Amount'),
                        'isAjax' => true,
                        'hidden' => $disable,
                        'confirm' => [
                            'title' => __('Delete Discount Amount (%1)', $title),
                            'message' => __('Are you sure you want to delete a discount amount?'),
                        ]
                    ];

                    $item[$name]['delete_qty_Limit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::URL_PATH_DELETE_QUANTIY_LIMIT,
                            ['id' => $item['entity_id']]
                        ),
                        'label' => __('Delete Quantity Limit'),
                        'isAjax' => true,
                        'hidden' => $disable,
                        'confirm' => [
                            'title' => __('Delete Quantity Limit (%1)', $title),
                            'message' => __('Are you sure you want to delete a quantity limit?'),
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $typeId
     * @return bool
     */
    private function checkProductTypeDisplay($typeId)
    {
        switch ($typeId) {
            case DownloadablePType::TYPE_DOWNLOADABLE:
            case ProductType::TYPE_SIMPLE:
            case ProductType::TYPE_VIRTUAL:
                return false;
            case ConfigurableType::TYPE_CODE:
            case BundleProductType::TYPE_CODE:
            case GroupedProductType::TYPE_CODE:
                return true;
            default:
                return true;
        }
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
