<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

/**
 * Class CacheContext
 */
class CacheContext extends \Magento\Framework\Indexer\CacheContext
{
    /**
     * @var array
     */
    protected $allowExcludeEntityIds = [];

    /**
     * @var array
     */
    protected $excludeEntityIds = [];

    /**
     * @var array
     */
    protected $extendEntityIds = [];

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function registerEntities($cacheTag, $ids)
    {
        if ($this->getAllowExcludeEntityIds($cacheTag)) {
            $ids = array_diff($ids, $this->getExcludeEntityIds($cacheTag));
        }

        parent::registerEntities($cacheTag, $ids);
        return $this;
    }

    /**
     * Set flag to allow exclude entity ids
     *
     * @param string $cacheTag
     * @param bool $allow
     * @return $this
     */
    public function setAllowExcludeEntityIds($cacheTag, $allow)
    {
        $this->allowExcludeEntityIds[$cacheTag] = $allow;
        return $this;
    }

    /**
     * Get flag to allow exclude entity ids
     *
     * @param $cacheTag
     * @return bool
     */
    public function getAllowExcludeEntityIds($cacheTag)
    {
        if (!isset($this->allowExcludeEntityIds[$cacheTag])) {
            return false;
        }
        return $this->allowExcludeEntityIds[$cacheTag];
    }

    /**
     * Set exclude entity ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function setExcludeEntityIds($cacheTag, $ids)
    {
        $this->excludeEntityIds[$cacheTag] = $ids;
        return $this;
    }

    /**
     * Get exclude entity ids
     *
     * @param $cacheTag
     * @return array
     */
    public function getExcludeEntityIds($cacheTag)
    {
        if (isset($this->excludeEntityIds[$cacheTag])) {
            return $this->excludeEntityIds[$cacheTag];
        }
        return [];
    }

    /**
     * Clear excluded entity ids
     *
     * @param $cacheTag
     * @return $this
     */
    public function clearExcludeEntityIds($cacheTag)
    {
        if (isset($this->excludeEntityIds[$cacheTag])) {
            unset($this->excludeEntityIds[$cacheTag]);
        }
        return $this;
    }

    /**
     * Register extend entities ids
     *
     * @param string $identifier
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function registerExtendEntityIds($identifier, $cacheTag, $ids)
    {
        if (!isset($this->extendEntityIds[$identifier])) {
            $this->extendEntityIds[$identifier] = [];
        }
        $this->extendEntityIds[$identifier][$cacheTag] =
            array_merge($this->getExtendRegisteredEntityIds($identifier, $cacheTag), $ids);
        return $this;
    }

    /**
     * Returns registered entities
     *
     * @param string $identifier
     * @param string $cacheTag
     * @return array
     */
    public function getExtendRegisteredEntityIds($identifier, $cacheTag)
    {
        if (!isset($this->extendEntityIds[$identifier])) {
            return [];
        }
        if (!isset($this->extendEntityIds[$identifier][$cacheTag])) {
            return [];
        }
        return $this->extendEntityIds[$identifier][$cacheTag];
    }

    /**
     * Returns identities
     *
     * @param string $identifier
     * @return array
     */
    public function getExtendIdentities($identifier)
    {
        if (!isset($this->extendEntityIds[$identifier])) {
            return [];
        }
        $identities = [];
        foreach ($this->extendEntityIds[$identifier] as $cacheTag => $ids) {
            foreach ($ids as $id) {
                $identities[] = $cacheTag . '_' . $id;
            }
        }
        return $identities;
    }

    /**
     * Clear excluded entity ids
     *
     * @param $identifier
     * @param $cacheTag
     * @return $this
     */
    public function clearExtendEntityIds($identifier, $cacheTag)
    {
        if (!isset($this->extendEntityIds[$identifier])) {
            return $this;
        }
        if (isset($this->extendEntityIds[$identifier][$cacheTag])) {
            unset($this->extendEntityIds[$identifier][$cacheTag]);
            if (empty($this->extendEntityIds[$identifier])) {
                unset($this->extendEntityIds[$identifier]);
            }
        }
        return $this;
    }
}
