<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Plugin\Observer\CatalogInventory;

use Innovo\CacheImprove\Model\Indexer\AbstractEntitiesProcessor;
use Magento\Framework\Event\Observer as EventObserver;
use Innovo\CacheImprove\Helper\Data as CacheHelper;
use Innovo\CacheImprove\Model\CacheContext as CacheImproveContext;
use Innovo\CacheImprove\Model\AffectedProductsContext;
use Innovo\CacheImprove\Model\Indexer\Stock\EntitiesProcessor;
use Magento\Catalog\Model\Product;

/**
 * Mark entities ids to use cache
 */
class ReindexQuoteInventoryObserver
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $stockIndexerProcessor;

    /**
     * @var EntitiesProcessor
     */
    protected $entitiesProcessor;

    /**
     * @var AffectedProductsContext
     */
    protected $affectedProductsContext;

    /**
     * @var CacheHelper
     */
    protected $helper;

    /**
     * @var CacheImproveContext
     */
    protected $cacheContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param EntitiesProcessor $entitiesProcessor
     * @param AffectedProductsContext $affectedProductsContext
     * @param CacheHelper $helper
     * @param CacheImproveContext $cacheContext
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        EntitiesProcessor $entitiesProcessor,
        AffectedProductsContext $affectedProductsContext,
        CacheHelper $helper,
        CacheImproveContext $cacheContext,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->entitiesProcessor = $entitiesProcessor;
        $this->affectedProductsContext = $affectedProductsContext;
        $this->helper = $helper;
        $this->cacheContext = $cacheContext;
        $this->logger = $logger;
    }

    /**
     * Mark entities ids to use cache
     *
     * @param \Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver $subject
     * @param \Closure $proceed
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver $subject,
        \Closure $proceed,
        EventObserver $observer
    ) {
        if (!$this->helper->getCiAllow($this->session, true)) {
            $proceed($observer);
            return;
        }

        if ($this->stockIndexerProcessor->isIndexerScheduled()) {
            $proceed($observer);
            return;
        }

        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            $allProductIds = $this->getAllProductIds($quote);
            if (empty($allProductIds)) {
                $proceed($observer);
                return;
            }

            $this->initEntitiesProcessor(
                $this->affectedProductsContext->getEntities(),
                $allProductIds
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

            $proceed($observer);

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

            // Clear affected products context
            $this->affectedProductsContext->clearEntities();
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
     * @return $this
     */
    public function initEntitiesProcessor($affectedProductIds, $allProductIds)
    {
        $this->entitiesProcessor->setType(AbstractEntitiesProcessor::PROCESSING_TYPE_INDEX)
            ->setAffectedProductIds($affectedProductIds)
            ->setAllProductIds($allProductIds)
            ->setIsAffectCategories($this->helper->isStockIndexerCacheAffectedByCategories())
            ->setIsScheduledIndexer($this->stockIndexerProcessor->isIndexerScheduled());
        return $this;
    }

    /**
     * Get all product ids from quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getAllProductIds($quote)
    {
        $productIds = [];
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }
        return $productIds;
    }
}
