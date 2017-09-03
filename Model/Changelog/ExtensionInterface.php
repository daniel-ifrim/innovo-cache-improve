<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Changelog;

interface ExtensionInterface
{
    /**
     * Get use cache column name
     *
     * @return string
     */
    public function getColumnName();

    /**
     * Extension changelog table schema
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @return void
     */
    public function addExtensionSchema($changelog);

    /**
     * Mark changelog entities to use or not to use clean cache
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @param array $entityIds
     * @param array $oldVersionId
     * @param array $currentVersionId
     * @param bool $useCache
     * @return $this
     */
    public function updateUseCache(
        $changelog,
        $entityIds,
        $oldVersionId = [],
        $currentVersionId = [],
        $useCache = true
    );

    /**
     * Retrieve list of entity ids that use or not use clean cache
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @param bool $useCache
     * @param bool $fromVersionId
     * @param bool $toVersionId
     * @return array
     */
    public function retrieveUseCacheEntityIds($changelog, $useCache = true, $fromVersionId, $toVersionId);

    /**
     * Normalize boolean value into int
     *
     * @param bool $useCache
     * @return int
     */
    public function normalizeUseCache($useCache);
}
