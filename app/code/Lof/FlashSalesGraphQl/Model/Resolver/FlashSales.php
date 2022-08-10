<?php
/**
 * FlashSales
 *
 * @copyright Copyright © 2021 Landofcoder. All rights reserved.
 * @author    landofcoder@gmail.com
 */
namespace Lof\FlashSalesGraphQl\Model\Resolver;

use Lof\FlashSalesGraphQl\Model\Resolver\DataProvider\FlashSale as FlashSaleDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class FlashSales
 * @package Lof\FlashSalesGraphQl\Model\Resolver
 */
class FlashSales implements ResolverInterface
{
    /**
     * @var FlashSaleDataProvider
     */
    private $flashSaleDataProvider;

    /**
     * @param FlashSaleDataProvider $flashSaleDataProvider
     */
    public function __construct(
        FlashSaleDataProvider $flashSaleDataProvider
    ) {
        $this->flashSaleDataProvider = $flashSaleDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $searchResult = $this->flashSaleDataProvider->getFlashSales($args);
        return [
            'total_count' => $searchResult->getTotalCount(),
            'items'       => $this->getFlashSalesData($searchResult->getItems()),
        ];
    }

    /**
     * @param $flashSales
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getFlashSalesData($flashSales)
    {
        $flashSalesData = [];
        foreach ($flashSales as $flashSale) {
            if (!is_numeric($flashSale)) {
                $flashSalesData[$flashSale->getFlashSalesId()] =
                    $this->flashSaleDataProvider->getFlashSaleById($flashSale->getFlashSalesId());
            }
        }
        return $flashSalesData;
    }
}
