<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Plugin\Mview;

use Innovo\CacheImprove\Model\Indexer\AbstractEntitiesProcessor;
use Innovo\CacheImprove\Model\CacheContext as CacheImproveContext;
use Innovo\CacheImprove\Model\Indexer\Stock\EntitiesProcessor;
use Innovo\CacheImprove\Model\View\ExtensionInterface as ViewExtensionInterface;
use Magento\Catalog\Model\Product;

/**
 * Class RegisterUseCacheEntities Plugin
 */
class RegisterUseCacheEntities
{
    /**
     * @var \Innovo\CacheImprove\Helper\Data
     */
    protected $helper;

    /**
     * @var ViewExtensionInterface
     */
    protected $viewExtension;

    /**
     * @var CacheImproveContext
     */
    protected $cacheContext;

    /**
     * @var EntitiesProcessor
     */
    protected $entitiesProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Innovo\CacheImprove\Helper\Data $helper
     * @param ViewExtensionInterface $viewExtension
     * @param CacheImproveContext $cacheContext
     * @param EntitiesProcessor $entitiesProcessor
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Innovo\CacheImprove\Helper\Data $helper,
        ViewExtensionInterface $viewExtension,
        CacheImproveContext $cacheContext,
        EntitiesProcessor $entitiesProcessor,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->viewExtension = $viewExtension;
        $this->cacheContext = $cacheContext;
        $this->entitiesProcessor = $entitiesProcessor;
        $this->logger = $logger;
    }

    /**
     * Mark entities ids to use cache
     *
     * @param \Magento\Framework\Mview\View $subject
     * @return array
     */
    public function beforeUpdate(\Magento\Framework\Mview\View $subject)
    {
        $collection = null;
        if (!$this->isActive($subject, $collection)) {
            return [];
        }

        try {
            $allProductIds = $this->viewExtension->retrieveUseCacheEntityIds($subject, null);
            if (empty($allProductIds)) {
                return [];
            }

            // Get entities ids affected by "don't clean cache". Priority to existing not affected.
            $notAffectedProductsIds = $this->viewExtension->retrieveUseCacheEntityIds($subject, false);
            $affectedProductsIds = array_diff($allProductIds, $notAffectedProductsIds);
            $this->initEntitiesProcessor(
                $affectedProductsIds,
                $allProductIds,
                $subject->isEnabled()
            );

            $this->entitiesProcessor->process();

            // Mark entities ids to use cache
            $affectedEntitiesIds = $this->entitiesProcessor->getFinalEntitiesIds();
            $notAffectedEntitiesIds = $this->entitiesProcessor->getFinalNotAffectedEntitiesIds();

            foreach ($affectedEntitiesIds as $cacheTag => $ids) {
                if (!empty($ids)) {
                    $this->cacheContext->setAllowExcludeEntityIds($cacheTag, true);
                    $this->cacheContext->setExcludeEntityIds($cacheTag, $ids);
                }
            }

            // Clean additional cache types by tags
            $extendCacheTypes = $this->helper->getFpcCacheTypeCleanTags();
            if (!empty($extendCacheTypes) && !empty($notAffectedEntitiesIds)) {
                foreach ($extendCacheTypes as $cacheType) {
                    foreach ($notAffectedEntitiesIds as $cacheTag => $ids) {
                        if (!empty($ids)) {
                            $this->cacheContext->registerExtendEntityIds(
                                $cacheType,
                                $cacheTag,
                                $ids
                            );
                        }
                    }
                }
            }

            // Add additional not affected product ids
            $additionalNotAffectedIds = $this->entitiesProcessor->getAdditionalNotAffectedIds(Product::CACHE_TAG);
            if (!empty($additionalNotAffectedIds)) {
                $this->cacheContext->registerEntities(Product::CACHE_TAG, $additionalNotAffectedIds);
            }

            // Force add product ids to cache context
            if ($this->helper->isForceFpcAddProductTags()) {
                if (
                    is_array($notAffectedEntitiesIds) &&
                    isset($notAffectedEntitiesIds[Product::CACHE_TAG]) &&
                    count($notAffectedEntitiesIds[Product::CACHE_TAG])
                ) {
                    $this->cacheContext->registerEntities(
                        Product::CACHE_TAG,
                        $notAffectedEntitiesIds[Product::CACHE_TAG]
                    );
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [];
    }

    /**
     * Clean marked entities ids
     *
     * @param \Magento\Framework\Mview\View $subject
     * @return void
     */
    public function afterUpdate(\Magento\Framework\Mview\View $subject)
    {
        $collection = null;
        if (!$this->isActive($subject, $collection)) {
            return;
        }

        try {
            $affectedEntitiesIds = $this->entitiesProcessor->getFinalEntitiesIds();
            $notAffectedEntitiesIds = $this->entitiesProcessor->getFinalNotAffectedEntitiesIds();
            if (empty($affectedEntitiesIds) && empty($notAffectedEntitiesIds)) {
                return;
            }

            // Clear tags markers
            foreach ($affectedEntitiesIds as $cacheTag => $ids) {
                if (!empty($ids)) {
                    $this->cacheContext->setAllowExcludeEntityIds($cacheTag, false);
                    $this->cacheContext->clearExcludeEntityIds($cacheTag);
                }
            }

            // Clear additional cache types tags markers
            $extendCacheTypes = $this->helper->getFpcCacheTypeCleanTags();
            if (!empty($extendCacheTypes) && !empty($notAffectedEntitiesIds)) {
                foreach ($extendCacheTypes as $cacheType) {
                    foreach ($notAffectedEntitiesIds as $cacheTag => $ids) {
                        if (!empty($ids)) {
                            $this->cacheContext->clearExtendEntityIds(
                                $cacheType,
                                $cacheTag
                            );
                        }
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Init entities processor
     *
     * @param array $affectedProductIds
     * @param array $allProductIds
     * @param bool $isScheduled
     * @return $this
     */
    public function initEntitiesProcessor($affectedProductIds, $allProductIds, $isScheduled)
    {
        $this->entitiesProcessor->setType(AbstractEntitiesProcessor::PROCESSING_TYPE_MVIEW)
            ->setAffectedProductIds($affectedProductIds)
            ->setAllProductIds($allProductIds)
            ->setIsAffectCategories($this->helper->isStockIndexerCacheAffectedByCategories())
            ->setIsScheduledIndexer($isScheduled);

        if ($this->helper->isFpcAddParentProductTags()) {
            $this->entitiesProcessor->setIsProcessAdditionalIds(true);
        }

        return $this;
    }

    /**
     * Get can run plugin
     *
     * @param \Magento\Framework\Mview\View $view
     * @param null|\Magento\Framework\Mview\View\CollectionInterface $collection
     * @return bool
     */
    protected function isActive($view, $collection)
    {
        if (!$this->helper->isActive()) {
            return false;
        }

        if (!$view->isEnabled()) {
            return false;
        }

        if (!(
            $this->viewExtension->isAffectedView($view) ||
            $this->viewExtension->isRelatedAffectedView($view, $collection)
        )) {
            return false;
        }

        if (!($view->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE)) {
            return false;
        }

        return true;
    }
}
