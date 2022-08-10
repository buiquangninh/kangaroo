<?php
/**
 * FlashSale
 *
 * @copyright Copyright © 2021 Landofcoder. All rights reserved.
 * @author    landofcoder@gmail.com
 */

namespace Lof\FlashSalesGraphQl\Model\Resolver\DataProvider;

use Lof\FlashSales\Api\FlashSalesRepositoryInterface;
use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;

/**
 * Class FlashSale
 * @package Lof\FlashSalesGraphQl\Model\Resolver\DataProvider
 */
class FlashSale
{
    /**
     * @var FlashSalesRepositoryInterface
     */
    private $flashsalesRepository;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @param FlashSalesRepositoryInterface $flashsalesRepository
     * @param Builder $builder
     */
    public function __construct(
        FlashSalesRepositoryInterface $flashsalesRepository,
        Builder $builder
    ) {
        $this->builder = $builder;
        $this->flashsalesRepository = $flashsalesRepository;
    }

    public function getFlashSales($args)
    {
        $searchCriteria = $this->builder->build('FlashSales', $args);
        $searchCriteria->setCurrentPage($args['currentPage']);
        $searchCriteria->setPageSize($args['pageSize']);

        return $this->flashsalesRepository->getList($searchCriteria);
    }

    /**
     * Fetch black data by either id or Flash Sale ID field
     * @param int $flashSaleId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFlashSaleById(int $flashSaleId)
    {
        $flashSale = $this->flashsalesRepository->get($flashSaleId);
        return $this->convertFlashSaleData($flashSale);
    }

    private function convertFlashSaleData(FlashSalesInterface $flashSale)
    {
        return [
            FlashSalesInterface::FLASHSALES_ID => $flashSale->getFlashsalesId(),
            FlashSalesInterface::EVENT_NAME => $flashSale->getEventName(),
            FlashSalesInterface::STATUS => $flashSale->getStatus(),
            FlashSalesInterface::STATUS_LABEL => $this->getStatusLabel((int)$flashSale->getStatus()),
            FlashSalesInterface::CONDITIONS_SERIALIZED => $flashSale->getConditionsSerialized(),
            FlashSalesInterface::CATEGORY_ID => $flashSale->getCategoryId(),
            FlashSalesInterface::IS_PRIVATE_SALE => $flashSale->getIsPrivateSale(),
            FlashSalesInterface::FROM_DATE => $flashSale->getFromDate(),
            FlashSalesInterface::TO_DATE => $flashSale->getToDate(),
            FlashSalesInterface::CREATED_AT => $flashSale->getCreatedAt(),
            FlashSalesInterface::UPDATED_AT => $flashSale->getUpdatedAt(),
            FlashSalesInterface::DISPLAY_CART_MODE => $flashSale->getDisplayCartMode(),
            FlashSalesInterface::HEADER_BANNER_IMAGE => $flashSale->getHeaderBannerImage(),
            FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG => $flashSale->getIsDefaultPrivateConfig(),
            FlashSalesInterface::CART_BUTTON_TITLE => $flashSale->getCartButtonTitle(),
            FlashSalesInterface::RESTRICTED_LANDING_PAGE => $flashSale->getRestrictedEventLandingPage(),
            FlashSalesInterface::GRANT_CHECKOUT_ITEMS => $flashSale->getGrantCheckoutItems(),
            FlashSalesInterface::GRANT_CHECKOUT_ITEMS_GROUPS => $flashSale->getGrantCheckoutItemsGroups(),
            FlashSalesInterface::GRANT_EVENT_PRODUCT_PRICE => $flashSale->getGrantEventProductPrice(),
            FlashSalesInterface::GRANT_EVENT_PRODUCT_PRICE_GROUPS => $flashSale->getGrantEventProductPriceGroups(),
            FlashSalesInterface::GRANT_EVENT_VIEW => $flashSale->getGrantEventView(),
            FlashSalesInterface::GRANT_EVENT_VIEW_GROUPS => $flashSale->getGrantEventViewGroups(),
            FlashSalesInterface::IS_ACTIVE => $flashSale->getIsActive(),
            FlashSalesInterface::MESSAGE_HIDDEN_ADD_TO_CART => $flashSale->getMessageHiddenAddToCart(),
            FlashSalesInterface::DISPLAY_PRODUCT_MODE => $flashSale->getDisplayProductMode(),
            FlashSalesInterface::SORT_ORDER => $flashSale->getSortOrder(),
            FlashSalesInterface::THUMBNAIL_IMAGE => $flashSale->getThumbnailImage(),
            FlashSalesInterface::MODEL => $flashSale,
        ];
    }

    /**
     * Used for display label by time in PWA
     * @param $status
     * @return mixed
     */
    private function getStatusLabel($status)
    {
        $statuses = [
            __('COMING SOON'),
            __('COMING SOON'),
            __('ACTIVE'),
            __('ACTIVE'),
            __('ENDED')
        ];

        return $statuses[$status];
    }
}
