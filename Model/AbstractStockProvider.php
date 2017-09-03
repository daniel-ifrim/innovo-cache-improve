<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Class AbstractStockProvider
 */
class AbstractStockProvider
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Normalize null scopeId
     *
     * @param $scopeId
     * @return int
     */
    public function normalizeScopeId($scopeId)
    {
        if ($scopeId === null) {
            $scopeId = $this->stockConfiguration->getDefaultScopeId();
        }
        return $scopeId;
    }

    /**
     * Get empty stock data array
     *
     * @param $productId
     * @param int $scopeId
     * @return array
     */
    public function getEmptyStockData($productId, $scopeId)
    {
        return [
            'innv_ci_stock_status' => 0,
            'is_in_stock' => 0,
            'product_id' => $productId,
            'website_id' => $scopeId,
            'qty' => 0
        ];
    }
}
