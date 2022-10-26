<?php

namespace Magenest\AffiliateClickCount\Ui\Component\Listing\Column\Count;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magenest\AffiliateClickCount\Model\ResourceModel\Affiliate\Collection as AffiliateClickCountCollection;

/**
 * Custom Display Qty Of Grid
 */
class getCountReferalUrl extends Column
{
    CONST ALIAS_TABLE = "magenest_affiliateclickcount_affiliatecountclick";
    /**
     * @var AffiliateClickCountCollection
     */
    private AffiliateClickCountCollection $affiliateCollection;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepository  $customerRepository,
        AffiliateClickCountCollection $affiliateCollection,
        array $components = [],
        array $data = []
    ) {
        $this->affiliateCollection = $affiliateCollection;
        $this->customerRepository = $customerRepository;
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
                $count = $this->affiliateCollection->join(
                    ['maa' => self::ALIAS_TABLE],
                    "main_table.referal_url = maa.referal_url AND main_table.affiliate_code = maa.affiliate_code",
                    []
                )->getSize();
                $item[$fieldName] = $count;
            }
        }

        return $dataSource;
    }
}
