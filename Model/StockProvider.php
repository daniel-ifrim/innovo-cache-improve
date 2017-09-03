<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

use Innovo\CacheImprove\Model\StockRegistryStorage as StockRegistryStorageCache;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\StockState;

/**
 * Class StockProvider
 */
class StockProvider extends AbstractStockProvider
{
    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var StockState
     */
    protected $stockState;

    /**
     * @var StockRegistryStorageCache
     */
    protected $stockRegistryStorageCache;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockState $stockState
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemInterfaceFactory $stockItemFactory,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockState $stockState
    ) {
        parent::__construct($stockConfiguration);
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockState = $stockState;
    }

    /**
     * Retrieve stock item
     *
     * @param int $productId
     * @param null|int $scopeId
     * @param bool $force
     * @return StockItemInterface
     */
    public function getStockItem($productId, $scopeId = null, $force = false)
    {
        if ($scopeId === null) {
            $scopeId = $this->normalizeScopeId($scopeId);
        }

        $stockItem = $this->getStockRegistryStorage()->getStockItem($productId, $scopeId);
        if ($stockItem === null || $force) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                // @see \Magento\CatalogInventory\Model\StockManagement::registerProductsSale()
                $canSubtractQty = $stockItem->getItemId() && $this->canSubtractQty($stockItem);
                if (!$canSubtractQty || !$this->stockConfiguration->isQty($stockItem->getTypeId())) {
                    $stockStatus = $stockItem->getIsInStock();
                    // Don't verify qty to: composite products and products that can't subtract qty
                    $this->getStockRegistryStorage()->setStockItem($productId, $scopeId, $stockItem);
                    $this->getStockRegistryStorage()->setVerifiedStockStatus($productId, $scopeId, $stockStatus);
                    return $stockItem;
                }

                // Verify stock to get out of stock items
                $stockStatus = $stockItem->getIsInStock();
                if (!$this->stockState->verifyStock($productId, $stockItem->getWebsiteId())
                    || $this->stockState->verifyNotification(
                        $productId,
                        $stockItem->getWebsiteId()
                    )
                ) {
                    $stockStatus = 0;
                }
                $this->getStockRegistryStorage()->setStockItem($productId, $scopeId, $stockItem);
                $this->getStockRegistryStorage()->setVerifiedStockStatus($productId, $scopeId, $stockStatus);
            } else {
                $this->getStockRegistryStorage()->removeStockItem($productId, $scopeId);
                $this->getStockRegistryStorage()->removeVerifiedStockStatus($productId, $scopeId);
                $stockItem = $this->stockItemFactory->create();
            }
        }
        return $stockItem;
    }

    /**
     * Check if is possible subtract value from item qty
     *
     * @see \Magento\CatalogInventory\Model\StockManagement::canSubtractQty()
     * @param StockItemInterface $stockItem
     * @return bool
     */
    public function canSubtractQty($stockItem)
    {
        return $stockItem->getManageStock() && $this->stockConfiguration->canSubtractQty();
    }

    /**
     * Get products stock data values
     *
     * @param int[] $productIds
     * @param null|int $scopeId
     * @param bool $force
     * @return array
     */
    public function getProductsStockData($productIds, $scopeId = null, $force = false)
    {
        $data = [];
        foreach ($productIds as $productId) {
            $data[$productId] = $this->getProductStockData($productId, $scopeId, $force);
        }
        return $data;
    }

    /**
     * Retrieve product data values
     *
     * @param int $productId
     * @param null|int $scopeId
     * @param bool $force
     * @return array
     */
    public function getProductStockData($productId, $scopeId = null, $force = false)
    {
        if ($scopeId === null) {
            $scopeId = $this->normalizeScopeId($scopeId);
        }

        $stockItem = $this->getStockItem($productId, $scopeId, $force);
        if (is_object($stockItem)) {
            $data = $stockItem->toArray();
            $data['innv_ci_stock_status'] = $this->getStockRegistryStorage()
                ->getVerifiedStockStatus($productId, $scopeId);
            return $data;
        } else {
            return $this->getEmptyStockData($productId, $scopeId);
        }
    }

    /**
     * Retrieve stock data of products that are affected by 'don't clean cache'
     *
     * @param [] $items array with product id as key and purchased qty as value
     * @param null|int $scope_id
     * @param bool $force
     * @return array
     */
    public function getAffectedProductsStocks($items, $scope_id = null, $force = false)
    {
        return $this->getProductsStockData(array_keys($items), $scope_id, $force);
    }

    /**
     * @return StockRegistryStorageCache
     */
    public function getStockRegistryStorage()
    {
        if (null === $this->stockRegistryStorageCache) {
            $this->stockRegistryStorageCache = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Innovo\CacheImprove\Model\StockRegistryStorage::class);
        }
        return $this->stockRegistryStorageCache;
    }
}
