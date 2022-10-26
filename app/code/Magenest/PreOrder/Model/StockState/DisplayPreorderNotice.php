<?php
namespace Magenest\PreOrder\Model\StockState;

use Magenest\PreOrder\Helper\PreOrderProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

class DisplayPreorderNotice
{
    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var PreOrderProduct
     */
    private $helper;

    /**
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param PreOrderProduct $helper
     * @throws NoSuchEntityException
     */
    public function __construct(
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory   $productSalableResultFactory,
        ProductRepositoryInterface             $productRepository,
        StoreManagerInterface                  $storeManager,
        PreOrderProduct                        $helper
    ) {
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->productSalableResultFactory   = $productSalableResultFactory;
        $this->productRepository             = $productRepository;
        $this->storeManager                  = $storeManager;
        $this->helper                        = $helper;

        $this->storeId = $this->storeManager->getStore()->getId();
    }

    /**
     * @param string $productId
     * @return ProductSalableResultInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $productId)
    {
        $product = $this->productRepository->getById($productId, false, $this->storeId);
        if ($this->helper->isPreOrderProduct($product)) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'pre-order-product',
                    'message' => __($this->helper->getStockStatusLabel($product))
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
