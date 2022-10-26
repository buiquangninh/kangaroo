<?php
/**
 * Copyright © AffiliateMultiLevelUpdate All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateMultiLevelUpdate\Cron;

use Magenest\Affiliate\Block\Account\MembershipClass;
use Magenest\Affiliate\Model\ResourceModel\Account\Collection as AccountCollection;
use Magenest\Affiliate\Model\AccountFactory;
use Magenest\Affiliate\Model\ResourceModel\Group\CollectionFactory as GroupCollection;

class UpgradeAffiliateRank
{

    protected $logger;

    private $accountCollection;

    private $account;

    private $groupCollectionFactory;
    /**
     * @var MembershipClass
     */
    private $affiliateHelper;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        AccountCollection $collection,
        AccountFactory $accountFactory,
        MembershipClass $affiliateHelper,
        GroupCollection $groupCollection
    ) {
        $this->groupCollectionFactory = $groupCollection;
        $this->account= $accountFactory;
        $this->accountCollection = $collection;
        $this->logger = $logger;
        $this->affiliateHelper = $affiliateHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $accountCollection = $this->accountCollection->addFieldToSelect(['account_id','group_id'])->join(
            ['so' => $this->accountCollection->getTable('sales_order')],
            'main_table.customer_id = so.customer_id',
            ['total_sales' => new \Zend_Db_Expr('SUM(subtotal)'), 'qty_order' => new \Zend_Db_Expr('SUM(entity_id)')]
        )->join(
            ['mag' => $this->accountCollection->getTable('magenest_affiliate_group')],
            'main_table.group_id = mag.group_id',
            ['approval_time', 'created_at']
        );

        $accountCollection = $accountCollection->getSelect()->where('so.created_at >= mag.created_at')->group('main_table.customer_id');

        $accountCollection = $accountCollection->getConnection()->fetchAll($accountCollection);

        $account = $this->account->create();

        foreach ($accountCollection as $accountData) {
            $accountAffiliateData = $account->load($accountData['account_id']);
            $getUpdateMembershipRank = $this->affiliateHelper->getUpgradeAffiliateGroupId($accountData['group_id']);
            $getUpdateGroupData = $this->groupCollectionFactory->create()->addFieldToFilter('group_id', ['eq' => $getUpdateMembershipRank])->getLastItem();
            $data = [];

            $checkDate = date('Y-m-d', strtotime($getUpdateGroupData->getData()['created_at'] . '+ ' . $getUpdateGroupData->getData()['approval_time'] . ' days'));
            if (strtotime($checkDate) <= strtotime(date('Y-m-d'))) {
                $groupsRevenueToReach = $this->groupCollectionFactory->create()->setOrder('revenue_to_reach', 'DESC');
                // check update affiliate rank
                foreach ($groupsRevenueToReach as $group) {
                    if ($accountData['total_sales'] >= $group->getData()['revenue_to_reach'] && $accountData['qty_order'] >= $group->getData()['qty_order'] && $accountData['group_id'] != $group->getData('group_id')) {
                        $data['group_id'] = $group->getId();
                        $data['created_at'] = date('Y-m-d H:i:s');
                        if ($group->getData('additional_reward_by_revenue') == 1) {
                            $data['total_commission'] = $accountData['total_commission'] + $accountData['total_sales'] * $group->getData('reward_value') / 100;
                        }
                        $accountAffiliateData->addData($data);
                        $accountAffiliateData->save();
                        break;
                    }
                }
                // check keep rank or downgrade rank
                if (empty($data)) {
                    $groupsRevenueToKeep = $this->groupCollectionFactory->create()->setOrder('revenue_to_keep', 'ASC');
                    foreach ($groupsRevenueToKeep as $group) {
                        if ($accountData['total_sales'] < $group->getData()['revenue_to_keep']) {
                            $data['group_id'] = $group->getId();
                            $data['created_at'] = date('Y-m-d H:i:s');
                            if ($group->getData('additional_reward_by_revenue') == 1) {
                                $data['total_commission'] = $accountData['total_commission'] + $accountData['total_sales'] * $group->getData('reward_value') / 100;
                            }
                            $accountAffiliateData->addData($data);
                            $accountAffiliateData->save();
                            break;
                        }
                    }
                }
            }
        }
    }
}
