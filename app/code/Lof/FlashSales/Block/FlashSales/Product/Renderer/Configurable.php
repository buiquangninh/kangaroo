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

namespace Lof\FlashSales\Block\FlashSales\Product\Renderer;

use Lof\FlashSales\Helper\CountDownData;
use Lof\FlashSales\Helper\View;
use Lof\FlashSales\Model\DateResolver;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Lof\FlashSales\Helper\Data as FlashSalesData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{

    /**
     * @var FlashSalesData
     */
    protected $helperData;

    /**
     * @var CountDownData
     */
    protected $countDownData;

    /**
     * @var View
     */
    protected $_viewHelper;

    /**
     * @var DateResolver
     */
    protected $dateResolver;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * Configurable constructor.
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param FlashSalesData $helperData
     * @param View $viewHelper
     * @param DecoderInterface $jsonDecoder
     * @param DateResolver $dateResolver
     * @param CountDownData $countDownData
     * @param array $data
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param UrlBuilder|null $imageUrlBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        FlashSalesData $helperData,
        View $viewHelper,
        DecoderInterface $jsonDecoder,
        DateResolver $dateResolver,
        CountDownData $countDownData,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        UrlBuilder $imageUrlBuilder = null
    ) {
        $this->_viewHelper = $viewHelper;
        $this->jsonDecoder = $jsonDecoder;
        $this->countDownData = $countDownData;
        $this->dateResolver = $dateResolver;
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data,
            $swatchAttributesProvider,
            $imageUrlBuilder
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJsonConfig()
    {
        $config = $this->jsonDecoder->decode(parent::getJsonConfig(), true);
        $config['loffsSwatchOptionsUrl'] = $this->getSwatchOptionsCallback();
        $config['loffsChildProductData'] = $this->getChildProductData();
        $config['loffsProductHeaderStyle'] = $this->helperData->getProductHeaderStyle();
        $config['loffsTopContainerSelector'] = $this->helperData->getPageMainSelector();
        $config['loffsProductInfoPriceSelector'] = $this->helperData->getProductInfoPriceSelector();
        $config['loffsTimeZone'] = $this->getConfigStoreTimezone();
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return array
     */
    public function getChildProductData()
    {
        $childProducts = [];
        $productAll = $this->getAllowProducts();
        foreach ($productAll as $childProduct) {
            $childProductId = $childProduct->getId();
            if ($this->countDownData->getFlashSalesWithChildProduct($childProductId) != null) {
                $flashSales = $this->countDownData->getFlashSalesWithChildProduct($childProductId);
                $childProducts[$childProductId][] = [
                    'flashsales_id' => $flashSales->getFlashSalesId(),
                    'discount_amount_html'=> $this->_viewHelper->discountAmountHtml($childProduct)
                ];
            }
        }
        return $childProducts;
    }

    /**
     * Get Swatch Options callback url.
     *
     * @return string
     */
    public function getSwatchOptionsCallback()
    {
        return $this->getUrl('lof_flashsales/ajax/addcountdownhtml', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigStoreTimezone()
    {
        return $this->dateResolver->getConfigStoreTimezone();
    }
}
