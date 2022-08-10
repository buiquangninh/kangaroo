<?php

namespace Magenest\RewardPoints\Observer;

use Magenest\RewardPoints\Model\ReferralPointsFactory;
use Magenest\RewardPoints\Model\Rule;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddPointsReferredRegistry
 * @package Magenest\RewardPoints\Observer
 */
class AddPointsReferredRegistry implements ObserverInterface
{
    /**
     * @var ReferralPointsFactory
     */
    protected $referralPointsFactory;

    /**
     * AddPointsReferredRegistry constructor.
     * @param ReferralPointsFactory $referralPointsFactory
     */
    public function __construct(
        ReferralPointsFactory $referralPointsFactory
    ) {
        $this->referralPointsFactory = $referralPointsFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $rule = $observer->getEvent()->getRule();
        if (!in_array($rule->getCondition(), [
            'referafriend',
            Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED,
            Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_REGISTER,
            Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_PLACE_ORDER
        ])) {
            return;
        }
        $referralPoints = $this->referralPointsFactory->create()->load($rule->getId(), 'rule_id');

        // Add more data to current rule before adding to registry
        $rule->setData('points_referred', $referralPoints->getPointsReferred() + 0);
    }
}
