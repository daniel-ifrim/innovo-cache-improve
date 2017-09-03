<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\View;

interface ExtensionInterface
{
    /**
     * Get all affected views ids by use cache
     *
     * @return array
     */
    public function getAffectedViewsIds();

    /**
     * Get views for which cache is not cleaned in default Magento code
     *
     * @return array
     */
    public function getNonCacheableViewsIds();

    /**
     * Is view affected by use cache
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @return bool
     */
    public function isAffectedView($view);

    /**
     * Is view not affected by cache cleaning in default Magento code
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @return bool
     */
    public function isNonCacheableView($view);

    /**
     * Is view linked to any affected other view
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return bool
     */
    public function isRelatedAffectedView($view, $collection = null, $selfAffected = true);

    /**
     * Get related changelog names by views ids
     *
     * @param \Magento\Framework\Mview\ViewInterface[] $views
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return array
     */
    public function getRelatedChangelogNamesByViews($views, $collection = null);

    /**
     * Mark view and linked views to use or not to use clean cache by entity ids
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param array $entityIds
     * @param array $oldVersionsIds
     * @param array $currentVersionsIds
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @param bool $useCache
     */
    public function updateUseCache(
        $view,
        $entityIds,
        $oldVersionsIds = [],
        $currentVersionsIds = [],
        $useCache = true,
        $collection = null
    );

    /**
     * Retrieve list of entity ids that use or not use clean cache
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param bool $useCache
     * @param bool $fromVersionId
     * @param bool $toVersionId
     * @return array
     */
    public function retrieveUseCacheEntityIds($view, $useCache = true, $fromVersionId = null, $toVersionId = null);
}
