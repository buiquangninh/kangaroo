<?php
namespace Magenest\PreOrder\Plugin;

use Magenest\PreOrder\Model\StockState\DisplayPreorderNotice;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\DataObject;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\ProductSalabilityError;
use Magento\InventorySales\Plugin\StockState\CheckQuoteItemQtyPlugin;
use Psr\Log\LoggerInterface;

class CheckQuoteItemQty
{
    /**
     * @var DisplayPreorderNotice
     */
    private $displayPreorderNotice;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DisplayPreorderNotice $displayPreorderNotice
     * @param LoggerInterface $logger
     */
    public function __construct(DisplayPreorderNotice $displayPreorderNotice, LoggerInterface $logger)
    {
        $this->logger                = $logger;
        $this->displayPreorderNotice = $displayPreorderNotice;
    }

    /**
     * @param CheckQuoteItemQtyPlugin $subject
     * @param DataObject $result
     * @param StockStateInterface $origin
     * @param \Closure $originalProceed
     * @param $productId
     * @return DataObject
     */
    public function afterAroundCheckQuoteItemQty(
        CheckQuoteItemQtyPlugin $subject,
        DataObject              $result,
        StockStateInterface     $origin,
        \Closure                $originalProceed,
        $productId
    ) {
        try {
            $productSalableResult = $this->displayPreorderNotice->execute($productId);
            if ($productSalableResult->getErrors()) {
                /** @var ProductSalabilityError $error */
                foreach ($productSalableResult->getErrors() as $error) {
                    $result->setMessage($error->getMessage());
                }
            }
        } catch (\Throwable $e) {
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $result->setMessage(__("Unexpected error happened when validating preorder products."));
        }

        return $result;
    }
}
