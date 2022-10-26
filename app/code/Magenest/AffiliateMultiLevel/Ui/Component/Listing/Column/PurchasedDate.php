<?php

namespace Magenest\AffiliateMultiLevel\Ui\Component\Listing\Column;

use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Custom Display Qty Of Grid
 */
class PurchasedDate extends Column
{
    /**
     * @var CollectionFactory
     */
    protected $salesOrderCollection;
    /**
     * @var PriceHelper
     */
    protected $pricingHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        PriceHelper $priceHelper,
        CollectionFactory $collection
    ) {
        $this->pricingHelper = $priceHelper;
        $this->salesOrderCollection = $collection;
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
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $collection = $this->salesOrderCollection->create()
                    ->addFieldToFilter('customer_id', $item['customer_id']);
                $collection->getSelect()
                    ->reset('columns')
                    ->columns('COUNT(entity_id) as entity_id')
                    ->columns('SUM(subtotal) as subtotal')
                    ->group('customer_id');
            }
        }

        return $dataSource;
    }

    private function formatPrice($price)
    {
        return $this->pricingHelper->currency($price);
    }
}
