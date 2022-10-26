<?php
/**
 * Copyright © CustomFlashSale All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\CustomFlashSale\Block\Flashsales;

use Lof\FlashSales\Model\FlashSalesRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria;
use Lof\FlashSales\Model\AppliedProductsRepository;
use \Magento\Framework\Api\FilterBuilder;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Framework\App\ActionInterface;
use \Magento\Framework\Url\EncoderInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use \Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class Index extends \Magento\Framework\View\Element\Template
{
    CONST LOF_FLASH_SALES_MEDIA = "lofflashsales/display_settings";
    /**
     * @var FlashSalesRepository
     */
    protected FlashSalesRepository  $flashSalesRepository;
    /**
     * @var SearchCriteria
     */
    protected SearchCriteriaInterface $searchCriteria;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    /**
     * @var AppliedProductsRepository
     */
    protected AppliedProductsRepository $appliedProductsRepository;
    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;
    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $_productFactory;
    /**
     * @var ImageBuilder
     */
    protected ImageBuilder $imageBuilder;
    /**
     * @var EncoderInterface|null
     */
    private $urlEncoder;
    /**
     * @var AbstractProduct
     */
    protected $abstractProduct;
    /**
     * @var UrlInterface
     */
    protected UrlInterface $_urlInterface;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var PriceHelper
     */
    protected PriceHelper $_priceHelper;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        SearchCriteriaInterface $searchCriteria,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        FlashSalesRepository  $flashSalesRepository,
        AppliedProductsRepository $appliedProductsRepository,
        \Magento\Framework\View\Element\Template\Context $context,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        ImageBuilder $imageBuilder,
        EncoderInterface $encoder,
        AbstractProduct $abstractProduct,
        Http $response,
        UrlInterface $urlInterface,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PriceHelper $priceHelper,
        array $data = []
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->appliedProductsRepository = $appliedProductsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->searchCriteria = $searchCriteria;
        $this->flashSalesRepository = $flashSalesRepository;
        $this->productRepository = $productRepository;
        $this->_productFactory = $productFactory;
        $this->imageBuilder = $imageBuilder;
        $this->urlEncoder = $encoder;
        $this->abstractProduct = $abstractProduct;
        $this->response = $response;
        $this->_urlInterface = $urlInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_priceHelper = $priceHelper;
        parent::__construct($context, $data);
    }

    public function getFlashSalesData() {
        $this->searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($this->filterBuilder->setField('is_active')
                ->setConditionType('eq')
                ->setValue(1)->create())
            ->addFilter($this->filterBuilder->setField('status')
                ->setConditionType('neq')
                ->setValue(4)->create())
            ->create()->setCurrentPage(1);
        return $this->flashSalesRepository->getList($this->searchCriteria);
    }

    public function getFlashSalesEndedData() {
        $this->searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($this->filterBuilder->setField('status')
                ->setConditionType('eq')
                ->setValue(4)->create())
            ->create()->setCurrentPage(1);
        return $this->flashSalesRepository->getList($this->searchCriteria);
    }

    public function getApplyProducts($flashSalesId) {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $searchCriteriaBuilder->addFilter(
            'flashsales_id',
            $flashSalesId
        );
        $searchCriteria = $searchCriteriaBuilder->create();

        return $this->appliedProductsRepository->getList($searchCriteria);
    }

    public function getProductCollection($productId) {
        return $this->productRepository->getById($productId);
    }

    public function getProductImage($productId, $imageId, $attributes = [])
    {
        $product = $this->_productFactory->create()->load($productId);
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    public function getProduct($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    public function getProductPriceHtml(
                $product,
                $priceType = null,
                $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = $arguments['price_id'] ?? 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = $arguments['include_container'] ?? true;
        $arguments['display_minimal_price'] = $arguments['display_minimal_price'] ?? true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    public function getAddToCartUrl($product, $additional = [])
    {
        $requestingPageUrl = $this->getRequest()->getParam('requesting_page_url');

        if (!empty($requestingPageUrl)) {
            $additional['useUencPlaceholder'] = true;
            $url = $this->abstractProduct->getAddToCartUrl($product, $additional);
            return str_replace('%25uenc%25', $this->urlEncoder->encode($requestingPageUrl), $url);
        }

        return $this->abstractProduct->getAddToCartUrl($product, $additional);
    }

    public function redirectUrl($url) {
        return $this->_urlInterface->getUrl($url);
    }

    public function getFlashSalesBanner() {
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $resizedURL . self::LOF_FLASH_SALES_MEDIA . '/' . $this->_scopeConfig->getValue('lofflashsales/display_settings/category_display_mode/default_banner');
    }

    public function formatPrice($price)
    {
        return $this->_priceHelper->currency($price);
    }


}

