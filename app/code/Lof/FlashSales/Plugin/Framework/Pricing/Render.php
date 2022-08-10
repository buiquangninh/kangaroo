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

namespace Lof\FlashSales\Plugin\Framework\Pricing;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\Adminhtml\System\Config\Source\ConfigData;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\App\State;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Customer\Model\Session;

class Render
{

    /**
     * @var State
     */
    private $state;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Render constructor.
     * @param Data $helperData
     * @param State $state
     * @param EncoderInterface $jsonEncoder
     * @param Session $customerSession
     */
    public function __construct(
        Data $helperData,
        State $state,
        EncoderInterface $jsonEncoder,
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->jsonEncoder = $jsonEncoder;
        $this->helperData = $helperData;
        $this->state = $state;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRender(
        PricingRender $subject,
        callable $proceed,
        $priceCode,
        SaleableInterface $saleableItem,
        array $arguments = []
    ) {
        $additionalHtml = '';
        if ($this->canDisplay($saleableItem, $arguments) && $this->state->getAreaCode() != Area::AREA_ADMINHTML) {
            $additionalHtml = $this->customAddToCartButton($saleableItem, $arguments);
        }
        return $proceed($priceCode, $saleableItem, $arguments) . $additionalHtml;
    }

    /**
     * @param $saleableItem
     * @param $arguments
     * @return string
     * @throws NoSuchEntityException
     */
    private function customAddToCartButton($saleableItem, $arguments)
    {
        $productId = 'loffs-product-button-' . $saleableItem->getId();
        return '<span data-role="loffs-privatesales-button" id="' . $productId . '"
               style="display: none !important;"></span>
            <script>
                require([
                    "jquery",
                     "Lof_FlashSales/js/loffsprivatesales"
                ], function ($, loffsPrivateSales) {
                    $(document).ready(function() {
                        $("#' . $productId . '").loffsPrivateSales(' .
                            $this->getJsonConfig($saleableItem, $arguments)
                        . ')
                    });
                });
            </script>';
    }

    /**
     * @param $saleableItem
     * @param $arguments
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function canDisplay($saleableItem, $arguments)
    {
        $isDisplayCartButton = $this->canShowCustomCartButton($saleableItem);
        $isPrivateSale = $saleableItem->getData('is_private_sale') == null ? true :
            !!$saleableItem->getData('is_private_sale');
        $isZone = (key_exists('zone', $arguments)
            && !in_array(
                $arguments['zone'],
                [PricingRender::ZONE_ITEM_LIST, PricingRender::ZONE_ITEM_VIEW]
            ));
        return $this->helperData->isEnabled()
            && ($saleableItem instanceof \Magento\Catalog\Model\Product)
            && $isPrivateSale
            && $isDisplayCartButton
            || $isZone;
    }

    /**
     * @param $saleableItem
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canShowCustomCartButton($saleableItem)
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        if ($saleableItem->getData('is_default_private_config')) {
            $mode = !!$saleableItem->getData('is_default_private_config')
                ? $this->helperData->getCheckoutItemsMode()
                : $saleableItem->getData('grant_checkout_items');
            switch ($mode) {
                case ConfigData::GRANT_CUSTOMER_GROUP:
                    $groups = $saleableItem->getData('is_default_private_config')
                        ? $this->helperData->getCheckoutItemsGroups()
                        : $this->helperData->getEventCheckoutItemsGroups($saleableItem);

                    if (!in_array($customerGroupId, $groups)) {
                        return !!$saleableItem->getData('is_default_private_config')
                            ? !$this->helperData->getDisplayCartMode()
                            : !$saleableItem->getData('display_cart_mode');
                    }
                    break;
                case ConfigData::GRANT_NONE:
                    return !!$saleableItem->getData('is_default_private_config')
                        ? !$this->helperData->getDisplayCartMode()
                        : !$saleableItem->getData('display_cart_mode');
            }
        }
        return false;
    }

    /**
     * @param $saleableItem
     * @param $arguments
     * @return bool|string
     * @throws NoSuchEntityException
     */
    private function getJsonConfig($saleableItem, $arguments)
    {
        return $this->jsonEncoder->encode([
            'price_code' => isset($arguments['price_type_code']) ? $arguments['price_type_code'] : false,
            'product_item_selector' => $this->helperData->getProductItemSelector(),
            'product_info_selector' => $this->helperData->getProductInfoMainSelector(),
            'actions_selector' => $this->helperData->getProductItemActionsSelector(),
            'html' => $this->helperData->customAddToCartButtonHtml($saleableItem),
        ]);
    }
}
