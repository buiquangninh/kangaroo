<?php
/**
 * Copyright © AffiliateMultiLevelUpdate All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateMultiLevelUpdate\Ui\Component\Listing\Column;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class PriceCurrency extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $_priceCurrency;
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->_priceCurrency = $priceCurrency;
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
                if (isset($item['revenue_to_reach']) || isset($item['revenue_to_keep'])) {
                    $item[$this->getData('name')] = is_numeric($item[$this->getData('name')]) ? $this->priceCurrency($item[$this->getData('name')]) : $item[$this->getData('name')];
                }
            }
        }

        return $dataSource;
    }

    private function priceCurrency($currency)
    {
        return $this->_priceCurrency->format($currency);
    }
}

