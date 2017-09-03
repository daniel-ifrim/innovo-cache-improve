<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Innovo\CacheImprove\Model\View;

use \Innovo\CacheImprove\Model\Changelog\ExtensionInterface as ChangelogExtensionInterface;

/**
 * Class View Extension
 *
 * @see \Magento\Framework\Mview\View
 */
class Extension implements ExtensionInterface
{
    /**
     * @var \Magento\Framework\Mview\View\CollectionFactory
     */
    protected $viewCollectionFactory;

    /**
     * @var ChangelogExtensionInterface
     */
    protected $changelogExtension;

    /**
     * @var array
     */
    protected $affectedViewsIds = [];

    /**
     * Views for which cache is not cleaned
     *
     * @var array
     */
    protected $nonCacheableViewsIds = [];

    /**
     * @param \Magento\Framework\Mview\View\CollectionFactory $viewCollectionFactory
     * @param ChangelogExtensionInterface $changelogExtension
     * @param $affectedViewsIds
     * @param $nonCacheableViewsIds
     */
    public function __construct(
        \Magento\Framework\Mview\View\CollectionFactory $viewCollectionFactory,
        ChangelogExtensionInterface $changelogExtension,
        $affectedViewsIds,
        $nonCacheableViewsIds
    ) {
        $this->viewCollectionFactory = $viewCollectionFactory;
        $this->changelogExtension = $changelogExtension;
        $this->affectedViewsIds = $affectedViewsIds;
        $this->nonCacheableViewsIds = $nonCacheableViewsIds;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getAffectedViewsIds()
    {
        return $this->affectedViewsIds;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getNonCacheableViewsIds()
    {
        return $this->nonCacheableViewsIds;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @return bool
     */
    public function isAffectedView($view)
    {
        if (array_search($view->getId(), $this->affectedViewsIds) !== false) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @return bool
     */
    public function isNonCacheableView($view)
    {
        if (array_search($view->getId(), $this->nonCacheableViewsIds) !== false) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return bool
     */
    public function isRelatedAffectedView($view, $collection = null, $selfAffected = true)
    {
        if ($this->isNonCacheableView($view)) {
            return false;
        }

        if ($collection === null) {
            $collection = $this->viewCollectionFactory->create();
        }

        if ($selfAffected && $this->isAffectedView($view) && !$this->isNonCacheableView($view)) {
            return true;
        }

        // Traverse all affected views and find if current view is linked to any
        $viewList = $this->getEnabledViews($collection);
        /** @var \Magento\Framework\Mview\ViewInterface $aView */
        foreach ($viewList as $aView) {
            if ($view->getId() == $aView->getId()) {
                continue;
            }

            // Not an affected view
            if (!$this->isAffectedView($aView)) {
                continue;
            }

            $linkedViews = $this->getLinkedViews($view, $collection);
            /** @var \Magento\Framework\Mview\ViewInterface $aLinkedView */
            foreach ($linkedViews as $aLinkedView) {
                if ($aLinkedView->getId() == $view->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\ViewInterface[] $views
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return array
     */
    public function getRelatedChangelogNamesByViews($views, $collection = null)
    {
        $names = [];
        /** @var \Magento\Framework\Mview\View $view */
        foreach ($views as $view) {
            $name = $view->getChangelog()->getName();
            if (array_search($name, $names) === false) {
                $names[] = $name;
            }

            $linkedViews = $this->getLinkedViews($view, $collection);
            foreach ($linkedViews as $aView) {
                $name = $aView->getChangelog()->getName();
                if (array_search($name, $names) === false) {
                    $names[] = $name;
                }
            }
        }
        return $names;
    }

    /**
     * Retrieve list of linked views by a view
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @param bool $include Include original view too
     * @return \Magento\Framework\Mview\ViewInterface[]
     */
    public function getLinkedViews($view, $collection = null, $include = false)
    {
        $linkedViews = [];

        // Get view subscription names
        $subscriptionsNames = [];
        $subscriptions = $view->getSubscriptions();
        foreach ($subscriptions as $subscription) {
            $subscriptionsNames[] = $subscription['name'];
        }

        $viewList = $this->getEnabledViews($collection);
        foreach ($viewList as $aView) {
            /** @var \Magento\Framework\Mview\ViewInterface $aView */
            // Skip the current view
            if ($aView->getId() == $view->getId()) {
                continue;
            }

            // Non cacheable views are not considered
            if ($this->isNonCacheableView($aView)) {
                continue;
            }
            // Search in view subscriptions
            foreach ($aView->getSubscriptions() as $aSubscription) {
                if (array_search($aSubscription['name'], $subscriptionsNames) === false) {
                    continue;
                }
                $linkedViews[] = $aView;
            }
        }

        if ($include && !$this->isNonCacheableView($view)) {
            array_unshift($linkedViews, $view);
        }

        return $linkedViews;
    }

    /**
     * Retrieve all versions ids by a view
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param bool $related
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return array
     */
    public function getAllVersionsIdsByView($view, $related = true, $collection = null)
    {
        $versionsIds = [];

        if ($related) {
            $views = $this->getLinkedViews($view, $collection);
            array_unshift($views, $view);
        } else {
            $views = [$view];
        }

        /** @var \Magento\Framework\Mview\ViewInterface $aView */
        foreach ($views as $aView) {
            $changelog = $aView->getChangelog();
            $versionsIds[$aView->getId()] = $changelog->getVersion();
        }
        return $versionsIds;
    }

    /**
     * Get enabled views from collection
     *
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return \Magento\Framework\Mview\ViewInterface[]
     */
    public function getEnabledViews($collection = null)
    {
        if ($collection === null) {
            $collection = $this->viewCollectionFactory->create();
        }
        return $collection->getViewsByStateMode(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED);
    }

    /**
     * {@inheritdoc}
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
    ) {
        $views = $this->getLinkedViews($view, $collection, true);
        /** @var \Magento\Framework\Mview\ViewInterface $aView */
        foreach ($views as $aView) {
            if (!isset($oldVersionsIds[$view->getId()]) || !isset($currentVersionsIds[$view->getId()])) {
                continue;
            }

            $changelog = $aView->getChangelog();
            $oVersionId = $oldVersionsIds[$view->getId()];
            $cVersionId = $currentVersionsIds[$view->getId()];
            $this->changelogExtension->updateUseCache($changelog, $entityIds, $oVersionId, $cVersionId, $useCache);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param bool $useCache
     * @param bool $fromVersionId
     * @param bool $toVersionId
     * @return array
     */
    public function retrieveUseCacheEntityIds($view, $useCache = true, $fromVersionId = null, $toVersionId = null)
    {
        if ($fromVersionId === null) {
            $fromVersionId = $view->getState()->getVersionId();
        }
        if ($toVersionId === null) {
            $toVersionId = $view->getChangelog()->getVersion();
        }

        $entityIds = $this->changelogExtension->retrieveUseCacheEntityIds(
            $view->getChangelog(),
            $useCache,
            $fromVersionId,
            $toVersionId
        );

        return $entityIds;
    }
}
