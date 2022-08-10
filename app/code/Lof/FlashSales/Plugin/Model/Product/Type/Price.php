<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Plugin\Model\Product\Type;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\ResourceModel\FlashSalesFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends Product\Type\Price
{

    /**
     * @var FlashSalesPricesStorage
     */
    protected $flashSalesPricesStorage;

    /**
     * @var FlashSalesFactory
     */
    protected $resourceFlashSalesFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Price constructor.
     *
     * @param FlashSalesPricesStorage $flashSalesPricesStorage
     * @param FlashSalesFactory $resourceFlashSalesFactory
     * @param Data $helperData
     * @param RuleFactory $ruleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ScopeConfigInterface $config
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FlashSalesPricesStorage $flashSalesPricesStorage,
        FlashSalesFactory $resourceFlashSalesFactory,
        Data $helperData,
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession,
        ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ScopeConfigInterface $config,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null
    ) {
        $this->helperData = $helperData;
        $this->flashSalesPricesStorage = $flashSalesPricesStorage;
        $this->resourceFlashSalesFactory = $resourceFlashSalesFactory;
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $tierPriceExtensionFactory
        );
    }

    /**
     * @param Product\Type\Price $subject
     * @param $result
     * @param $qty
     * @param $product
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetFinalPrice(Product\Type\Price $subject, $result, $qty, $product)
    {
        $storeId = $this->_storeManager->getStore($storeId = $product->getStoreId())->getId();
        if ($product->getCustomOption('simple_product') && $product->getCustomOption('simple_product')->getProduct()) {
            $product = $this->getConfigurableProduct($product);
        }
        return $this->receiveProductPrice($result, $storeId, $product);
    }

    /**
     * @param $product
     * @return Product
     */
    private function getConfigurableProduct($product)
    {
        /** @var Product $simpleProduct */
        $simpleProduct = $product->getCustomOption('simple_product')->getProduct();
        $simpleProduct->setCustomerGroupId($product->getCustomerGroupId());
        return $simpleProduct;
    }

    /**
     * @param $result
     * @param $storeId
     * @param $product
     * @return mixed
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function receiveProductPrice($result, $storeId, $product)
    {
        $key = "{$storeId}|{$product->getId()}";
        if (!$this->flashSalesPricesStorage->hasFlashSalesPrice($key)) {
            $dateTime = $this->helperData->getCurrentDateTime();
            $price = $this->resourceFlashSalesFactory->create()->getFlashSalesPrice(
                $storeId,
                $product->getId(),
                $dateTime
            );
            $this->flashSalesPricesStorage->setFlashSalesPrice($key, $price);
        }
        if ($this->flashSalesPricesStorage->getFlashSalesPrice($key) !== false) {
            if ($this->helperData->getSellOverQuantityLimit()) {
                $appliedProduct = $this->helperData->getAppliedProductCollection()
                    ->addFieldToFilter('sku', $product->getSku())
                    ->getFirstItem();

                if ($appliedProduct->getQtyLimit() == null || $appliedProduct->getQtyLimit() == 0) {
                    return $product->getData('final_price');
                }
            }
            $finalPrice = min(
                $product->getData('final_price'),
                $this->flashSalesPricesStorage->getFlashSalesPrice($key)
            );

            if ($product->getLinksPurchasedSeparately()) {
                if ($linksIds = $product->getCustomOption('downloadable_link_ids')) {
                    $linkPrice = 0;
                    $links = $product->getTypeInstance()->getLinks($product);
                    foreach (explode(',', $linksIds->getValue()) as $linkId) {
                        if (isset($links[$linkId])) {
                            $linkPrice += $links[$linkId]->getPrice();
                        }
                    }
                    $finalPrice += $linkPrice;
                }
            }

            $product->setFinalPrice($finalPrice);
            return $finalPrice;
        }

        return $result;
    }
}
