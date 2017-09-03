<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Class StockRegistryProvider
 */
class StockRegistryProvider extends AbstractStockProvider
{
    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry
    ) {
        parent::__construct($stockConfiguration);
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Get products stock data from registry classes
     *
     * @param int[] $productIds
     * @param null|int $scopeId
     * @return array
     */
    public function getProductsStockData($productIds, $scopeId = null)
    {
        $data = [];
        foreach ($productIds as $productId) {
            $data[$productId] = $this->getProductStockData($productId, $scopeId);
        }
        return $data;
    }

    /**
     * Get product stock data from registry classes
     *
     * @param int $productId
     * @param null|int $scopeId
     * @return array
     */
    public function getProductStockData($productId, $scopeId = null)
    {
        if ($scopeId === null) {
            $scopeId = $this->normalizeScopeId($scopeId);
        }

        $stockItem = $this->stockRegistry
            ->getStockItem($productId, $scopeId);
        $data = $stockItem->toArray();
        $data['innv_ci_stock_status'] = $stockItem->getIsInStock();
        return $data;
    }

    /**
     * Get stock data of products that may be affected by 'don't clean cache'
     *
     * @param [] $items array with product id as key and purchased qty as value
     * @param null|int $scope_id
     * @return array
     */
    public function getAffectedProductsStocks($items, $scope_id = null)
    {
        return $this->getProductsStockData(array_keys($items), $scope_id);
    }
}
