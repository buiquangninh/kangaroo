<?php

namespace Magenest\AffiliateClickCount\Ui\Component\Listing\Column\Customer;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Custom Display Qty Of Grid
 */
class GetCustomerName extends Column
{
    /**
     * @var CustomerRepository
     */
    private CustomerRepository  $customerRepository;
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepository  $customerRepository,
        array $components = [],
        array $data = []
    ) {
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
                if (isset($item[$fieldName])) {
                    $customerRepository = $this->customerRepository->getById($item[$fieldName]);
                    $item[$fieldName] = $customerRepository->getFirstname() . " " . $customerRepository->getLastname();
                }
            }
        }

        return $dataSource;
    }
}
