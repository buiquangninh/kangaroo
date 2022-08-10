<?php

namespace Magenest\RewardPoints\Observer;

use Magenest\RewardPoints\Helper\Data;
use Magenest\RewardPoints\Model\ReferralPointsFactory;
use Magenest\RewardPoints\Model\Rule;
use Magenest\RewardPoints\Model\RuleFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Points implements ObserverInterface
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var ReferralPointsFactory
     */
    protected $referralPointsFactory;

    /**
     * Points constructor.
     * @param RuleFactory $ruleFactory
     * @param ReferralPointsFactory $referralPointsFactory
     */
    public function __construct(
        RuleFactory           $ruleFactory,
        ReferralPointsFactory $referralPointsFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->referralPointsFactory = $referralPointsFactory;
    }

    public function execute(Observer $observer)
    {
        if (Data::isReferAFriendModuleEnabled()) {
            $referralData = $observer->getData('referral_data');

            if (!isset($referralData['condition'])) return;

            // delete referring point record when change from refer a friend to other behavior rule
            if (!in_array(
                $referralData['condition'],
                [
                    'referafriend',
                    Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED,
                    Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_REGISTER,
                    Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_PLACE_ORDER,
                ]
            )) {
                $currentRule = $this->ruleFactory->create()->load($referralData['id']);
                $currentCondition = $currentRule->getCondition();
                if (!in_array($currentCondition, ['referafriend', 'earn_when_referee_clicked'])) return;
                $this->referralPointsFactory->create()->load($referralData['id'], 'rule_id')->delete();
                return;
            }

            // saving refer a friend rule, if exist, update, if not, create
            $referralPoints = $this->referralPointsFactory->create()->load($referralData['id'], 'rule_id');
            $referralPoints->setData('rule_id', $referralData['id']);
            $referralPoints->setData('points_referring', $referralData['points'] ?? 0);
            $referralPoints->setData('points_referred', $referralData['points_referred'] ?? 0);
            $referralPoints->save();
        } else {
            $ruleBeforeSave = $observer->getEvent()->getCurrentRule();
            if ($ruleBeforeSave->getCondition() == 'referafriend' && $ruleBeforeSave->getStatus()) $ruleBeforeSave->setStatus(0);
        }

        return;
    }
}
