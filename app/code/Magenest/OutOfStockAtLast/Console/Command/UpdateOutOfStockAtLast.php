<?php
/**
 * Magenest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magenest.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magenest.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magenest
 * @package     Magenest_StoreCredit
 * @copyright   Copyright (c) Magenest (https://www.magenest.com/)
 * @license     https://www.magenest.com/LICENSE.txt
 */

namespace Magenest\OutOfStockAtLast\Console\Command;

use Exception;
use Magenest\CustomSource\Model\Source\Area\Options;
use Magenest\OutOfStockAtLast\Model\ResourceModel\Inventory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;


/**
 * Class Uninstall
 * @package Magenest\StoreCredit\Console\Command
 */
class UpdateOutOfStockAtLast extends Command
{
    /**
     * @param \Magento\Framework\App\State $appState
     * @param Options $options
     * @param ProductFactory $productFactory
     * @param Registry $registry
     * @param Inventory $inventory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ResourceConnection           $resourceConnection,
        \Magento\Framework\App\State $appState,
        Options                      $options,
        ProductFactory               $productFactory,
        Registry                     $registry,
        Inventory                    $inventory,
        StoreManagerInterface        $storeManager,
        ProductRepository            $productRepository,
        string                       $name = null
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->appState = $appState;
        $this->options = $options;
        $this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('outofstockatlast:update')->setDescription('Updating out of stock at last data');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            $connection = $this->resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
            $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
            $select = $connection->select()
                ->from($productTable)
                ->where(
                    'out_of_stock_at_last_mien_bac IS NULL',
                )->orWhere(
                    'out_of_stock_at_last_mien_trung IS NULL',
                )->orWhere(
                    'out_of_stock_at_last_mien_nam IS NULL',
                );
            $result = $connection->fetchAll($select);
            $options = $this->options->toOptionArray();
            foreach ($result as $item) {
                if ($item['out_of_stock_at_last_mien_bac'] == null || $item['out_of_stock_at_last_mien_trung'] == null
                    || $item['out_of_stock_at_last_mien_nam'] == null) {
                    foreach ($options as $option) {
                        $this->registry->unregister('current_area');
                        $this->registry->register('current_area', $option['value']);
                        $value = $this->inventory->getStockStatus(
                            $item['sku'],
                            $this->storeManager->getStore($this->storeManager->getStore()->getId())->getWebsite()->getCode()
                        );
                        $connection->update(
                            $productTable,
                            [
                                'out_of_stock_at_last_' . $option['value'] => $value,
                            ],
                            ['entity_id = ?' => $item['entity_id']]
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
