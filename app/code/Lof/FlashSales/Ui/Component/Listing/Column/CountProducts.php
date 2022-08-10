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

use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class CountProducts extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var CollectionFactory
     */
    protected $appliedProductsCollectionFactory;

    /**
     * CountProducts constructor.
     * @param CollectionFactory $appliedProductsCollectionFactory
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        CollectionFactory $appliedProductsCollectionFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->appliedProductsCollectionFactory = $appliedProductsCollectionFactory;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $appliedProducts = $this->appliedProductsCollectionFactory
                    ->create()
                    ->addFieldToFilter('flashsales_id', $item['flashsales_id'])->count();
                $item[$this->getData('name')] = $appliedProducts;
            }
        }

        return $dataSource;
    }
}
