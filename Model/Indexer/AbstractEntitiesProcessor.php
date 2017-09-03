<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Indexer;

/**
 * Class AbstractEntitiesProcessor
 */
abstract class AbstractEntitiesProcessor
{
    const PROCESSING_TYPE_INDEX = 0;
    const PROCESSING_TYPE_MVIEW = 1;

    /**
     * @var array
     */
    protected $processedEntitiesIds = [];

    /**
     * @var array
     */
    protected $affectedEntitiesIds = [];

    /**
     * @var array
     */
    protected $additionalNotAffectedIds = [];

    /**
     * @var array
     */
    protected $allEntitiesIds = [];

    /**
     * @var bool
     */
    protected $isScheduledIndexer = false;

    /**
     * @var bool
     */
    protected $isProcessAdditionalIds = false;

    /**
     * @var int
     */
    protected $type = self::PROCESSING_TYPE_INDEX;

    /**
     * Process affected entities ids
     *
     * @return $this
     */
    abstract public function process();

    /**
     * Set affected entities ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function setAffectedEntitiesIds($cacheTag, $ids)
    {
        $this->affectedEntitiesIds[$cacheTag] = $ids;
        return $this;
    }

    /**
     * Get affected entities ids
     *
     * @param string $cacheTag
     * @return array
     */
    public function getAffectedEntitiesIds($cacheTag)
    {
        if (isset($this->affectedEntitiesIds[$cacheTag])) {
            return $this->affectedEntitiesIds[$cacheTag];
        }
        return [];
    }

    /**
     * Set additional not affected entities ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function setAdditionalNotAffectedIds($cacheTag, $ids)
    {
        $this->additionalNotAffectedIds[$cacheTag] = $ids;
        return $this;
    }

    /**
     * Get additional not affected entities ids
     *
     * @param string $cacheTag
     * @return array
     */
    public function getAdditionalNotAffectedIds($cacheTag)
    {
        if (isset($this->additionalNotAffectedIds[$cacheTag])) {
            return $this->additionalNotAffectedIds[$cacheTag];
        }
        return [];
    }

    /**
     * Set all entities ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function setAllEntitiesIds($cacheTag, $ids)
    {
        $this->allEntitiesIds[$cacheTag] = $ids;
        return $this;
    }

    /**
     * Get all entities ids
     *
     * @param string $cacheTag
     * @return array
     */
    public function getAllEntitiesIds($cacheTag)
    {
        if (isset($this->allEntitiesIds[$cacheTag])) {
            return $this->allEntitiesIds[$cacheTag];
        }
        return [];
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsScheduledIndexer($bool)
    {
        $this->isScheduledIndexer = $bool;
        return $this;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsProcessAdditionalIds($bool)
    {
        $this->isProcessAdditionalIds = $bool;
        return $this;
    }

    /**
     * Set processed entities ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function setProcessedEntitiesIds($cacheTag, $ids)
    {
        $this->processedEntitiesIds[$cacheTag] = $ids;
        return $this;
    }

    /**
     * Get processed entities ids
     *
     * @param string $cacheTag
     * @return array
     */
    public function getProcessedEntitiesIds($cacheTag)
    {
        if (isset($this->processedEntitiesIds[$cacheTag])) {
            return $this->processedEntitiesIds[$cacheTag];
        }
        return [];
    }

    /**
     * Get all processed entities ids
     *
     * @return array
     */
    public function getFinalEntitiesIds()
    {
        $final = [];
        foreach ($this->processedEntitiesIds as $cacheTag => $ids) {
            $final[$cacheTag] = $ids;
        }
        return $final;
    }

    /**
     * Get all processed not affected entities ids
     *
     * @return array
     */
    public function getFinalNotAffectedEntitiesIds()
    {
        $final = [];
        $affectedEntitiesIds = $this->getFinalEntitiesIds();
        foreach ($affectedEntitiesIds as $cacheTag => $ids) {
            $allIds = $this->getAllEntitiesIds($cacheTag);
            $final[$cacheTag] = array_diff($allIds, $ids);
        }
        return $final;
    }

    /**
     * Set processing type
     *
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get processing type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
