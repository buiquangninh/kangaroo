<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * User: leo
 * Date: 22/06/2022
 * Time: 14:47
 */
declare(strict_types=1);

namespace Lof\FlashSales\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Model\Product;

/**
 * Add grid column with status product data
 */
class StatusProduct extends Column
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Product $product
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Product $product,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->product = $product;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->product->load($row['sku']);
            $row['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::getOptionArray()[$product->getStatus()];
        }
        unset($row);

        return $dataSource;
    }
}
