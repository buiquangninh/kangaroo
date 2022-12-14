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



namespace Mirasvit\CustomerSegment\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;
use Mirasvit\CustomerSegment\Api\Data\SegmentInterface;

class Status implements ArrayInterface
{

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => SegmentInterface::STATUS_ACTIVE, 'label' => __('Active')],
            ['value' => SegmentInterface::STATUS_INACTIVE, 'label' => __('Inactive')]
        ];
    }
}