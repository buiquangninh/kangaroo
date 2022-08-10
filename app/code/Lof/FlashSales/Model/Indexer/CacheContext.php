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

namespace Lof\FlashSales\Model\Indexer;

class CacheContext extends \Magento\Framework\Indexer\CacheContext
{

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array  $ids
     * @return CacheContext
     */
    public function registerEntities($cacheTag, $ids)
    {
        $this->entities[ $cacheTag ] = $ids;

        return $this;
    }
}
