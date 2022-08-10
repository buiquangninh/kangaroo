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

namespace Lof\FlashSales\Observer;

use Lof\FlashSales\Helper\PermissionsData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ApplyPermissionsOnCategory
{

    /**
     * @var PermissionsData
     */
    protected $_permissionsData;

    /**
     * ApplyPermissionsOnCategory constructor.
     * @param PermissionsData $permissionsData
     */
    public function __construct(
        PermissionsData $permissionsData
    ) {
        $this->_permissionsData = $permissionsData;
    }

    /**
     * @param $category
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute($category)
    {
        if (!$this->_permissionsData->isAllowedCategoryView($category)) {
            $category->setIsActive(0);
            $category->setIsHidden(true);
        }

        return $this;
    }
}
