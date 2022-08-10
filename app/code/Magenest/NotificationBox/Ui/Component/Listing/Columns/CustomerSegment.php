<?php

namespace Magenest\NotificationBox\Ui\Component\Listing\Columns;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Mirasvit\CustomerSegment\Model\Config\Source\Segment;

class CustomerSegment extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $serializer;

    protected $customerSegment;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SerializerInterface $serializer,
        Segment $customerSegment,
        array $components = [],
        array $data = []
    ) {
        $this->customerSegment = $customerSegment;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['customer_segment'])) {
                    $customerSegments = '';
                    $listSegments = $this->serializer->unserialize($item['customer_segment']);
                    if (count($listSegments) == count($this->customerSegment->getAllOptions())) {
                        $item['customer_segment'] = 'All Customer Segment';
                    } else {
                        foreach ($listSegments as $listSegment) {
                            foreach ($this->customerSegment->getAllOptions() as $option) {
                                if ($listSegment == $option['value']) {
                                    $customerSegments .= $option['label'] . '<br>';
                                }
                            }
                        }
                        $item['customer_segment'] = html_entity_decode($customerSegments);
                    }
                }
            }
        }
        return $dataSource;
    }
}
