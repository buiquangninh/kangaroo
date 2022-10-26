<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Reward points extension
 * NOTICE OF LICENSE
 *
 * @author Magenest
 * @time: 02/11/2020 17:08
 */

namespace Magenest\RewardPoints\Model\Membership;

use \Magento\Customer\Model\ResourceModel\Group\Collection;

class CustomerGroups implements \Magento\Framework\Data\OptionSourceInterface
{
    private $customerGroups;

    public function __construct(
        Collection $customerGroupsCollection
    ) {
        $this->customerGroups = $customerGroupsCollection;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->customerGroups->addFieldToFilter('customer_group_id', ['neq' => 0])->toOptionArray();
    }
}
