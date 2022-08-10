<?php
/**
 * FlashSaleById
 *
 * @copyright Copyright © 2021 Landofcoder. All rights reserved.
 * @author    landofcoder@gmail.com
 */

namespace Lof\FlashSalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\FlashSalesGraphQl\Model\Resolver\DataProvider\FlashSale as FlashSaleDataProvider;
use function is_numeric;

class FlashSaleById implements ResolverInterface
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
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|GraphQlNoSuchEntityException|Value|mixed
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!isset($args['flashsales_id'])) {
            throw new GraphQlInputException(__('Flash Sales ID is required'));
        }
        $flashSaleData = [];
        try {
            if (is_numeric($args['flashsales_id'])) {
                $flashSaleData = $this->flashSaleDataProvider->getFlashSaleById($args['flashsales_id']);
            }
        } catch (NoSuchEntityException $e) {
            $flashSaleData = new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $flashSaleData;
    }
}
