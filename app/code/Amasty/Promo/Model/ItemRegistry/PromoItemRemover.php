<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Free Gift for Magento 2
*/
declare(strict_types=1);

namespace Amasty\Promo\Model\ItemRegistry;

use Amasty\Promo\Model\ResourceModel\Rule;
use Amasty\Promo\Model\ItemRegistry\PromoItemData;

class PromoItemRemover
{
    /**
     * @var Rule
     */
    private $rule;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @param PromoItemData[] $items
     * @return PromoItemData[]
     */
    public function execute(array $items): array
    {
        $ruleIds = [];
        $allSkus = [];
        $availableSkus = [];

        foreach ($items as $key => $item) {
            if (!in_array($item->getRuleId(), $ruleIds)) {
                $ruleIds[] = $item->getRuleId();
            }

            if (!in_array($item->getSku(), $allSkus)) {
                $allSkus[$key] = $item->getSku();
            }
        }

        $ruleSkus = $this->rule->isApplicable($ruleIds, 'sku');

        foreach ($ruleSkus as $skus) {
            $availableSkus[] = explode(',', $skus['sku']);
        }

        $availableSkus = array_merge([], ...$availableSkus);
        $availableSkus = array_map('trim', $availableSkus);

        foreach ($allSkus as $key => $sku) {
            if (!in_array($sku, $availableSkus)) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}
