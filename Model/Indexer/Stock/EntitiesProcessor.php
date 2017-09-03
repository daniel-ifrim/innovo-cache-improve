<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Indexer\Stock;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Innovo\CacheImprove\Model\CategoryProvider as CategoryProviderCache;
use Innovo\CacheImprove\Model\ProductProvider as ProductProviderCache;
use Innovo\CacheImprove\Model\Indexer\AbstractEntitiesProcessor;
use Innovo\CacheImprove\Model\RulesApplier;

/**
 * Class EntitiesProcessor
 */
class EntitiesProcessor extends AbstractEntitiesProcessor
{
    /**
     * @var CategoryProviderCache
     */
    protected $categoryProviderCache;

    /**
     * @var ProductProviderCache
     */
    protected $productProviderCache;

    /**
     * @var RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var bool
     */
    protected $isAffectCategories = false;

    /**
     * @param CategoryProviderCache $categoryProviderCache
     * @param ProductProviderCache $productProviderCache
     * @param RulesApplier $rulesApplier
     */
    public function __construct(
        CategoryProviderCache $categoryProviderCache,
        ProductProviderCache $productProviderCache,
        RulesApplier $rulesApplier
    ) {
        $this->categoryProviderCache = $categoryProviderCache;
        $this->productProviderCache = $productProviderCache;
        $this->rulesApplier = $rulesApplier;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function process()
    {
        // If stock indexer uses Mview than cache is not cleaned.
        if (
            $this->getType() == AbstractEntitiesProcessor::PROCESSING_TYPE_INDEX &&
            $this->isScheduledIndexer
        ) {
            $this->setProcessedEntitiesIds(Product::CACHE_TAG, []);
            if ($this->isAffectCategories) {
                $this->setProcessedEntitiesIds(Category::CACHE_TAG, []);
            }
            return $this;
        }

        // Add parent products ids. Changelogs tables contain only child product ids.
        if (
            $this->getType() == AbstractEntitiesProcessor::PROCESSING_TYPE_MVIEW &&
            $this->isScheduledIndexer
        ) {
            // Retrieve ids by child parent relation
            $allProductIds = $this->getAllProductIds();
            $childParentIds = $this->productProviderCache
                ->retrieveParentProductIds($allProductIds);

            // Set again all product ids including parent ids
            $parentIds = array_unique(array_values($childParentIds));
            $allProductIds = array_unique(array_merge($allProductIds, $parentIds));
            $this->setAllProductIds($allProductIds);

            // Set again affected product ids including parent ids
            $affectedProductIds = $this->rulesApplier->extendWithParentIds(
                $this->getAffectedProductIds(),
                $allProductIds,
                $childParentIds
            );
            $this->setAffectedProductIds($affectedProductIds);

            unset($affectedProductIds);
            unset($allProductIds);
        }

        // Set product ids affected by "don't clean cache"
        $this->setProcessedEntitiesIds(
            Product::CACHE_TAG,
            $this->getAffectedEntitiesIds(Product::CACHE_TAG)
        );

        // Force add parent product ids not affected by 'don't clean cache'
        if (
            $this->getType() == AbstractEntitiesProcessor::PROCESSING_TYPE_MVIEW &&
            $this->isScheduledIndexer &&
            $this->isProcessAdditionalIds
        ) {
            $allProductIds = $this->getAllEntitiesIds(Product::CACHE_TAG);
            // Get all final not affected product ids
            $notAffectedProductIds = array_diff($allProductIds, $this->getProcessedEntitiesIds(Product::CACHE_TAG));
            // Retrieve parent ids of not affected ids
            $additionalNotAffectedIds = $this->productProviderCache->retrieveParentProductIds(
                $notAffectedProductIds
            );
            $additionalNotAffectedIds = array_unique(array_values($additionalNotAffectedIds));
            $this->setAdditionalNotAffectedIds(
                Product::CACHE_TAG,
                $additionalNotAffectedIds
            );
        }

        $this->processCategories();

        return $this;
    }

    /**
     * Process categories ids
     *
     * @return $this
     */
    public function processCategories()
    {
        if (!$this->isAffectCategories) {
            return $this;
        }

        $affectedProductIds =$this->getAffectedEntitiesIds(Product::CACHE_TAG);
        // Retrieve products categories ids
        $affectedCategoriesIds = $this->categoryProviderCache
            ->retrieveCategoryIdsByProductIds($affectedProductIds);

        $allProductIds = $this->getAllEntitiesIds(Product::CACHE_TAG);
        $notAffectedProductIds = array_diff($allProductIds, $affectedProductIds);
        $notAffectedCategoriesIds = $this->categoryProviderCache
            ->retrieveCategoryIdsByProductIds($notAffectedProductIds);
        $affectedCategoriesIds = array_diff($affectedCategoriesIds, $notAffectedCategoriesIds);
        $this->setAllEntitiesIds(Category::CACHE_TAG, array_unique(
            array_merge($notAffectedCategoriesIds, $affectedCategoriesIds)
        ));

        // Set category ids affected by "don't clean cache"
        $this->setProcessedEntitiesIds(
            Category::CACHE_TAG,
            $affectedCategoriesIds
        );

        return $this;
    }

    /**
     * Set affected product ids
     *
     * @param array $ids
     * @return $this
     */
    public function setAffectedProductIds($ids)
    {
        $this->setAffectedEntitiesIds(Product::CACHE_TAG, $ids);
        return $this;
    }

    /**
     * Get affected product ids
     *
     * @return array
     */
    public function getAffectedProductIds()
    {
        return $this->getAffectedEntitiesIds(Product::CACHE_TAG);
    }

    /**
     * Set all product ids
     *
     * @param array $ids
     * @return $this
     */
    public function setAllProductIds($ids)
    {
        $this->setAllEntitiesIds(Product::CACHE_TAG, $ids);
        return $this;
    }

    /**
     * Get all product ids
     *
     * @return array
     */
    public function getAllProductIds()
    {
        return $this->getAllEntitiesIds(Product::CACHE_TAG);
    }

    /**
     * Set is affect categories
     *
     * @param bool $bool
     * @return $this
     */
    public function setIsAffectCategories($bool)
    {
        $this->isAffectCategories = $bool;
        return $this;
    }
}
