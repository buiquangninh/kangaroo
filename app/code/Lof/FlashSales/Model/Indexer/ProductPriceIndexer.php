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

use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

class ProductPriceIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    const INDEXER_ID = 'lof_flashsales_productprice';

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * ProductPriceIndexer constructor.
     * @param IndexBuilder $indexBuilder
     * @param ManagerInterface $eventManager
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager,
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->indexBuilder = $indexBuilder;
        $this->_eventManager = $eventManager;
    }

    /**
     * @param int[] $ids
     * @throws LocalizedException
     */
    public function execute($ids)
    {
        $this->indexBuilder->reindexByFlashSalesId($ids);
    }

    /**
     * @throws LocalizedException
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->getCacheManager()->clean($this->getIdentities());
    }

    /**
     * @param array $ids
     * @throws LocalizedException
     */
    public function executeList(array $ids)
    {
        $this->indexBuilder->reindexByFlashSalesId($ids);
    }

    /**
     * @param int $id
     * @throws LocalizedException
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $this->indexBuilder->reindexByFlashSalesId($id);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws LocalizedException
     */
    public function executeByFlashSalesId($id)
    {
        $this->indexBuilder->reindexByFlashSalesId($id);
    }

    /**
     * Get affected cache tags
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getIdentities()
    {
        return [
            \Magento\Catalog\Model\Category::CACHE_TAG,
            \Magento\Catalog\Model\Product::CACHE_TAG,
            \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
        ];
    }

    /**
     * Get indexer
     *
     * @return IndexerInterface
     */
    public function getIndexer()
    {
        return $this->indexerRegistry->get(static::INDEXER_ID);
    }

    /**
     * Check if indexer is on scheduled
     *
     * @return bool
     */
    public function isIndexerScheduled()
    {
        return $this->getIndexer()->isScheduled();
    }

    /**
     * Get cache manager
     *
     * @return CacheInterface|mixed
     * @deprecated 100.0.7
     */
    private function getCacheManager()
    {
        if ($this->cacheManager === null) {
            $this->cacheManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CacheInterface::class
            );
        }
        return $this->cacheManager;
    }
}
