<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 15/12/2021
 * Time: 08:58
 */

namespace Magenest\CustomAdvancedReports\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

class AssignedSalesPerson implements OptionSourceInterface
{
    protected $sourceCollection;

    /**
     * SourceOption constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->sourceCollection = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptionArray() as $key => $value) {
            $result[] = [
                'label' => $value,
                'value' => $key ?: null
            ];
        }

        return $result;
    }

    public function getOptionArray()
    {
        $collection = $this->sourceCollection->create();

        $collection->addFieldToFilter('is_salesperson', 1)->getData();

        $result = [];
        foreach ($collection as $one) {
            $result[$one->getData('user_id')] = $one->getData("firstname") . " ". $one->getData("lastname");
        }

        return $result;
    }
}
