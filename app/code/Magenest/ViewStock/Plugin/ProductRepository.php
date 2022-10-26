<?php
namespace Magenest\ViewStock\Plugin;

use Magenest\CustomSource\Helper\Data;
use Magenest\CustomSource\Model\Source\Area\Options;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\GetAllowedProductTypesForSourceItemManagementInterface;
use Psr\Log\LoggerInterface;

class ProductRepository
{
    /** @var IsSingleSourceModeInterface */
    private $isSingleSourceMode;

    /** @var Options */
    private $options;

    /** @var Data */
    private $dataHelper;

    /** @var GetAllowedProductTypesForSourceItemManagementInterface */
    private $getAllowedProductTypesForSourceItemManagement;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param GetAllowedProductTypesForSourceItemManagementInterface $getAllowedProductTypesForSourceItemManagement
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param Options $options
     * @param Data $dataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetAllowedProductTypesForSourceItemManagementInterface $getAllowedProductTypesForSourceItemManagement,
        IsSingleSourceModeInterface $isSingleSourceMode,
        Options $options,
        Data $dataHelper,
        LoggerInterface $logger
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->options = $options;
        $this->dataHelper = $dataHelper;
        $this->getAllowedProductTypesForSourceItemManagement = $getAllowedProductTypesForSourceItemManagement;
        $this->logger = $logger;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @return ProductInterface
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface           $result
    ): ProductInterface {
        $this->setExtensionAttributes($result);
        return $result;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @return ProductInterface
     */
    public function afterGetById(
        ProductRepositoryInterface $subject,
        ProductInterface           $result
    ): ProductInterface {
        $this->setExtensionAttributes($result);
        return $result;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param ProductSearchResultsInterface $result
     * @return ProductSearchResultsInterface
     */
    public function afterGetList(
        ProductRepositoryInterface    $subject,
        ProductSearchResultsInterface $result
    ): ProductSearchResultsInterface {
        foreach ($result->getItems() as $product) {
            $this->setExtensionAttributes($product);
        }

        return $result;
    }

    /**
     * @param ProductInterface $product
     * @return void
     */
    public function setExtensionAttributes(ProductInterface $product)
    {
        try {
            if ($extensionAttributes = $product->getExtensionAttributes()) {
                $sourceData = $this->getSourceData($product);
                if (!empty($sourceData)) {
                    $extensionAttributes->setSourceItems($sourceData);
                }
                $extensionAttributes->setUrl($product->getProductUrl());
                $extensionAttributes->setMedia($product->getMediaGalleryImages()->getItems());
                $product->setExtensionAttributes($extensionAttributes);
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getSourceData($product)
    {
        $result = [];
        if (!$this->isSingleSourceMode->execute()
            && in_array($product->getTypeId(), $this->getAllowedProductTypesForSourceItemManagement->execute())) {
            $areasOptionData = $this->options->toOptionArray();

            foreach ($areasOptionData as $areaOption) {
                $inventorySourceItems = $this->dataHelper->getDataIsSalableOfProduct(
                    $areaOption['value'],
                    [$product->getSku()]
                );

                foreach ($inventorySourceItems as $sourceItem) {
                    $result[] = new DataObject([
                        'area_name' => $areaOption['label'],
                        'area_code' => $areaOption['value'],
                        'qty' => round($sourceItem['qty']),
                    ]);
                }
            }
        }

        return $result;
    }
}
