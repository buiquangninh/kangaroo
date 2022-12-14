<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-customer-segment
 * @version   1.2.1
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CustomerSegment\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerSegmentCondition implements ObserverInterface
{
    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $conditions = $observer->getData('additional')->getConditions();
        if (!is_array($conditions)) {
            $conditions = [];
        }

        $conditions = array_merge_recursive($conditions, [
            [
                'value' => 'Mirasvit\CustomerSegment\Model\SalesRule\Rule\Condition\Segment',
                'label' => __('Customer Segment'),
            ]
        ]);

        $observer->getData('additional')->setConditions($conditions);
    }
}
