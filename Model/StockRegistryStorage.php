<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Class StockRegistryStorage
 */
class StockRegistryStorage
{
    /**
     * @var array
     */
    public $stockItems = [];

    /**
     * @var array
     */
    public $verifiedStockStatus = [];

    /**
     * Set stock item
     *
     * @param int $productId
     * @param int $scopeId
     * @param StockItemInterface $value
     */
    public function setStockItem($productId, $scopeId, StockItemInterface $value)
    {
        if (!isset($this->stockItems[$productId])) {
            $this->stockItems[$productId] = [];
        }
        $this->stockItems[$productId][$scopeId] = $value;
    }

    /**
     * Get stock item
     *
     * @param int $productId
     * @param int $scopeId
     * @return null|StockItemInterface
     */
    public function getStockItem($productId, $scopeId)
    {
        if (isset($this->stockItems[$productId]) && isset($this->stockItems[$productId][$scopeId])) {
            return $this->stockItems[$productId][$scopeId];
        }
        return null;
    }

    /**
     * Remove stock item
     *
     * @param $productId
     * @param null $scopeId
     */
    public function removeStockItem($productId, $scopeId = null)
    {
        if (isset($this->stockItems[$productId])) {
            if (isset($this->stockItems[$productId][$scopeId])) {
                unset($this->stockItems[$productId][$scopeId]);
            }
            if ($scopeId === null) {
                unset($this->stockItems[$productId]);
            }
        }
    }

    /**
     * Set verified stock status
     *
     * @param int $productId
     * @param int $scopeId
     * @param int $value
     */
    public function setVerifiedStockStatus($productId, $scopeId, $value)
    {
        if (!isset($this->verifiedStockStatus[$productId])) {
            $this->verifiedStockStatus[$productId] = [];
        }
        $this->verifiedStockStatus[$productId][$scopeId] = $value;
    }

    /**
     * Get verified stock status
     *
     * @param int $productId
     * @param int $scopeId
     * @return int
     */
    public function getVerifiedStockStatus($productId, $scopeId)
    {
        if (
            isset($this->verifiedStockStatus[$productId]) &&
            isset($this->verifiedStockStatus[$productId][$scopeId])
        ) {
            return $this->verifiedStockStatus[$productId][$scopeId];
        }
        return null;
    }

    /**
     * Remove verified stock status
     *
     * @param $productId
     * @param null $scopeId
     */
    public function removeVerifiedStockStatus($productId, $scopeId = null)
    {
        if (isset($this->verifiedStockStatus[$productId])) {
            if (isset($this->verifiedStockStatus[$productId][$scopeId])) {
                unset($this->verifiedStockStatus[$productId][$scopeId]);
            }
            if ($scopeId === null) {
                unset($this->verifiedStockStatus[$productId]);
            }
        }
    }
}
