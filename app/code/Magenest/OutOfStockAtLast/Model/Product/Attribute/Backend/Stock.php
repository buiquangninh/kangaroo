<?php

namespace Magenest\OutOfStockAtLast\Model\Product\Attribute\Backend;

use Magenest\CustomSource\Model\Source\Area\Options;
use Magenest\OutOfStockAtLast\Model\ResourceModel\Inventory;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Stock extends AbstractBackend
{
    const PREVENT_BEFORE_SAVE = "prevent_before_save";

    public function __construct(
        LoggerInterface       $logger,
        ScopeConfigInterface  $scopeConfig,
        StoreManagerInterface $storeManager,
        Inventory             $inventory,
        Options               $areaOptions,
        Registry              $registry
    )
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
        $this->areaOptions = $areaOptions;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\DataObject |\Magento\Catalog\Model\Product $object
     */
    public function beforeSave($object)
    {
        foreach ($this->areaOptions->toOptionArray() as $option) {
            $sku = $object->getSku();
            $this->registry->unregister('current_area');
                $this->registry->register('current_area', $option['value']);

                $value = $this->inventory->getStockStatus(
                    $sku,
                    $this->storeManager->getStore($this->storeManager->getStore()->getId())->getWebsite()->getCode()
                );
                $object->setCustomAttribute('out_of_stock_at_last_' . $option['value'], $value);
        }
        return $object;
    }
}
