<?php
namespace Magenest\PreOrder\Helper;

use Magenest\PreOrder\Model\System\Config\Source\OrderType;
use Magenest\PreOrder\Setup\Patch\Data\AddButtonLabelAttribute;
use Magenest\PreOrder\Setup\Patch\Data\AddOrderTypeAttribute;
use Magenest\PreOrder\Setup\Patch\Data\AddStockLabelAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PreOrderProduct extends \Magento\Framework\App\Helper\AbstractHelper
{
    const GLOBAL_ORDER_TYPE_CONFIG = 'magenest_preorder/general/order_type';
    const GLOBAL_BUTTON_LABEL_CONFIG = 'magenest_preorder/general/add_to_cart_label';
    const GLOBAL_STOCK_STATUS_LABEL = 'magenest_preorder/general/stock_status_label';

    /** @var int|null */
    private $storeId;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeId = $storeManager->getStore()->getId();
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function isPreOrderProduct($product)
    {
        return $product->getData(AddOrderTypeAttribute::ORDER_TYPE_DEFAULT_ATTRIBUTE)
            ? $this->scopeConfig->getValue(self::GLOBAL_ORDER_TYPE_CONFIG, ScopeInterface::SCOPE_STORES, $this->storeId) == OrderType::VALUE_PREORDER
            : $product->getData(AddOrderTypeAttribute::ORDER_TYPE_ATTRIBUTE) == OrderType::VALUE_PREORDER;
    }

    /**
     * @param $product
     * @return string
     */
    public function getAddToCartLabel($product)
    {
        return $product->getData(AddButtonLabelAttribute::BUTTON_LABEL_DEFAULT_ATTRIBUTE)
            ? $this->scopeConfig->getValue(self::GLOBAL_BUTTON_LABEL_CONFIG, ScopeInterface::SCOPE_STORES, $this->storeId)
            : $product->getData(AddButtonLabelAttribute::BUTTON_LABEL_ATTRIBUTE);
    }

    /**
     * @param $product
     * @return string
     */
    public function getStockStatusLabel($product)
    {
        return $product->getData(AddStockLabelAttribute::STOCK_LABEL_DEFAULT_ATTRIBUTE)
            ? $this->scopeConfig->getValue(self::GLOBAL_STOCK_STATUS_LABEL, ScopeInterface::SCOPE_STORES, $this->storeId)
            : $product->getData(AddStockLabelAttribute::STOCK_LABEL_ATTRIBUTE);
    }
}
