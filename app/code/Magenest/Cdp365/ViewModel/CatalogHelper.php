<?php
namespace Magenest\Cdp365\ViewModel;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class CatalogHelper implements ArgumentInterface
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param LoggerInterface $logger
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository, LoggerInterface $logger)
    {
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
    }

    /**
     * @param ProductInterface $product
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategoryList($product)
    {
        $count = 0;
        $result = [];
        foreach ($product->getCategoryIds() as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                if ($category->getLevel() >= 1) {
                    $result[] = $category->getName();
                    $count++;
                }

                if ($count >= 3) {
                    break;
                }
            } catch (\Throwable $e) {
                $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }

        return $result;
    }
}
