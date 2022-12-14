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



namespace Mirasvit\CsNewsletter\Api\Service;

use Magento\Newsletter\Model\Queue;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection as SubscriberCollection;

interface SubscriberFilterServiceInterface
{
    /**
     * Filter subscriber collection by segment.
     *
     * @param SubscriberCollection $collection
     * @param Queue $queue
     *
     * @return mixed
     */
    public function filterBySegment(SubscriberCollection $collection, Queue $queue);
}
