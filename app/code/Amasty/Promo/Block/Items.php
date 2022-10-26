<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Free Gift for Magento 2
*/

namespace Amasty\Promo\Block;

use Amasty\Promo\Model\ResourceModel\Rule;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data;

/**
 * Popup Items
 */
class Items extends \Magento\Framework\View\Element\Template
{
    public const REGULAR_PRICE = 0;

    public const FINAL_PRICE = 1;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Promo\Helper\Data
     */
    protected $promoHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $helperImage;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Catalog\Block\Product\View
     */
    private $productView;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $modelConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Amasty\Promo\Model\RuleFactory
     */
    protected  $rule;
    private $priceHelper;

    /**
     * @param \Amasty\Promo\Model\RuleFactory $rule
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amasty\Promo\Helper\Data $promoHelper
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Block\Product\View $productView
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Amasty\Promo\Model\Config $modelConfig
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param array $data
     */
    public function __construct(
        Data  $priceHelper,
        \Amasty\Promo\Model\RuleFactory $rule,
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Magento\Catalog\Helper\Image $helperImage,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Block\Product\View $productView,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\Store $store,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Promo\Model\Config $modelConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rule = $rule;
        $this->priceHelper = $priceHelper;
        $this->logger = $context->getLogger();
        $this->promoHelper = $promoHelper;
        $this->helperImage = $helperImage;
        $this->urlHelper = $urlHelper;
        $this->productRepository = $productRepository;
        $this->store = $store;
        $this->productView = $productView;
        $this->registry = $registry;
        $this->catalogHelper = $catalogHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->priceCurrency = $priceCurrency;
        $this->modelConfig = $modelConfig;
        $this->productMetadata = $productMetadata;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @return $this|bool|\Magento\Framework\Data\Collection\AbstractDb|null
     */
    public function getItems()
    {
        return $this->promoHelper->getNewItems();
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->helperImage;
    }

    /**
     * @return mixed|string
     */
    public function getCurrentBase64Url()
    {
        if ($this->hasData('current_url')) {
            return $this->getData('current_url');
        }

        return $this->urlHelper->getCurrentBase64Url();
    }

    /**
     * @return mixed
     */
    public function getSelectionMethod()
    {
        return $this->modelConfig->getScopeValue("messages/gift_selection_method");
    }

    /**
     * @return mixed
     */
    public function getGiftsCounter()
    {
        return $this->modelConfig->getScopeValue("messages/display_remaining_gifts_counter");
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('amasty_promo/cart/add');
    }

    /**
     * @return mixed
     */
    public function getShowPriceInPopup()
    {
        return $this->modelConfig->getScopeValue("messages/show_price_in_popup");
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getAddButtonName()
    {
        $popupTitle = $this->escapeHtml($this->modelConfig->getAddButtonName());

        if (!$popupTitle) {
            $popupTitle = __('Add to cart');
        }

        return $popupTitle;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $product = $this->productRepository->getById($product->getId());
        $price = $this->priceHelper->currency($product->getPrice(),true,false);
        return $price;
    }

    public function getProductPriceAfter(\Magento\Catalog\Model\Product $product){
        $regularPrice = $this->productRepository->getById($product->getId())->getPrice();
        $promo_id = array_key_first($this->promoHelper->getPromoItemsDataArray()['triggered_products']);
        $price = 0;
        $discount = $this->rule->create()->load($promo_id,'salesrule_id')->getItemsDiscount();
        if ($discount){
            $discountDetail = substr($discount,-1) ;
            if ($discountDetail == '%'){
                $afterDiscount = substr($discount, 0,-1);
                $price = $regularPrice *  (100 - (int)$afterDiscount) / 100;
            }
            else $price = $regularPrice - (float) $discount;
        }
        return $price;
    }
    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionsHtml(\Magento\Catalog\Model\Product $product)
    {
        $this->registry->register('current_product', $product);
        $optionsHtml = $this->getChildBlock('options_prototype')->setProduct($product)->toHtml();
        $this->registry->unregister('current_product');
        $optionsHtml = str_replace(
            "#product_addtocart_form",
            "#ampromo_items_form-" . $product->getId(),
            $optionsHtml
        );
        $optionsHtml = str_replace(
            "[data-role=priceBox]",
            ".price-box-" . $product->getId(),
            $optionsHtml
        );

        return $optionsHtml;
    }

    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJsonConfig(\Magento\Catalog\Model\Product $product)
    {
        $product = $this->productRepository->getById($product->getId());
        $this->registry->register('product', $product);
        $jsonConfig = $this->productView->getJsonConfig();

        if ($product->getTypeId() === 'giftcard') {
            $priceConfig = $this->jsonDecoder->decode($jsonConfig);
            if (isset($priceConfig['prices']['basePrice']['amount'])) {
                $baseAmount = &$priceConfig['prices']['basePrice']['amount'];
                $openAmountMin = $product->getOpenAmountMin() * $this->store->getCurrentCurrencyRate();
                if ($baseAmount > $openAmountMin) {
                    $baseAmount = $openAmountMin;
                }
            }

            $jsonConfig = $this->jsonEncoder->encode($priceConfig);
        }
        if ($product->getTypeId() === BundleType::TYPE_CODE) {
            $priceConfig = $this->jsonDecoder->decode($jsonConfig);
            if (isset($priceConfig['prices']['basePrice']['amount'])) {
                $baseAmount = &$priceConfig['prices']['basePrice']['amount'];
                $baseAmount = $product->getPrice() * $this->store->getCurrentCurrencyRate();
            }
            $jsonConfig = $this->jsonEncoder->encode($priceConfig);
        }

        $this->registry->unregister('product');

        return $jsonConfig;
    }

    /**
     * Return true if product has options
     *
     * @param $product
     *
     * @return bool
     */
    public function hasOptions($product)
    {
        if ($product->getTypeInstance()->hasOptions($product)) {
            return true;
        }

        return false;
    }

    /**
     * @param int $productId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getLogMessage());
        }
    }

    /**
     * @param $product
     *
     * @return string
     */
    public function getGiftCardPrice($product)
    {
        /** @var \Magento\Framework\Pricing\Render\RendererPool $productPrices */
        $productPrices = $this->getChildBlock('render.product.prices');
        $data = $productPrices->getData('giftcard');
        /** @var \Magento\Framework\Pricing\Render\PriceBoxRenderInterface $priceRender */
        $priceRender = $productPrices->createPriceRender('final_price', $product, $data['prices']['final_price']);

        return $priceRender->toHtml();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->productMetadata->getEdition() !== \Magento\Framework\App\ProductMetadata::EDITION_NAME) {
            $this->addChild('giftcard_prototype', \Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard::class);
            $this->getChildBlock('giftcard_prototype')
                ->setTemplate('Magento_GiftCard::product/view/type/giftcard.phtml');
        }

        return parent::toHtml();
    }

    public function getStatus()
    {
        $promo_id = array_key_first($this->promoHelper->getPromoItemsDataArray()['triggered_products']);
        return $this->rule->create()->load($promo_id,'salesrule_id')->getItemsDiscount();
    }
}
