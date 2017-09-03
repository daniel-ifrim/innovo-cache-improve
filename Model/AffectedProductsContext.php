<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * A transport/context class to pass products data affected by 'don't clear cache'
 *
 * Class AffectedProductsContext
 */
class AffectedProductsContext
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var array
     */
    protected $entities = [];

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * Register entity ids
     *
     * @param null|int $scopeId
     * @param array $ids
     * @return $this
     */
    public function registerEntities($ids, $scopeId = null)
    {
        $scopeId = $this->normalizeScopeId($scopeId);
        $this->entities[$scopeId] =
            array_merge($this->getEntities($scopeId), $ids);
        $this->entities[$scopeId] = array_unique($this->entities[$scopeId]);
        return $this;
    }

    /**
     * Set entities ids
     *
     * @param array $ids
     * @param null|int $scopeId
     * @return $this
     */
    public function setEntities($ids, $scopeId = null)
    {
        $scopeId = $this->normalizeScopeId($scopeId);
        $this->entities[$scopeId] = $ids;
        return $this;
    }

    /**
     * Get entities ids
     *
     * @param null|int $scopeId
     * @return array
     */
    public function getEntities($scopeId = null)
    {
        $scopeId = $this->normalizeScopeId($scopeId);
        if (isset($this->entities[$scopeId])) {
            return $this->entities[$scopeId];
        }
        return [];
    }

    /**
     * Clear entities
     *
     * @param null|int $scopeId
     * @return $this
     */
    public function clearEntities($scopeId = null)
    {
        $scopeId = $this->normalizeScopeId($scopeId);
        if (isset($this->entities[$scopeId])) {
            unset($this->entities[$scopeId]);
        }
        return $this;
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
}
