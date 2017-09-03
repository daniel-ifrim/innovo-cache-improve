<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

use Innovo\CacheImprove\Model\Validator;

/**
 * Class RulesApplier
 */
class RulesApplier
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @param \Innovo\CacheImprove\Model\Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Apply rules to get product ids that do not need clean cache
     *
     * @param array $oldStocks
     * @param array $newStocks
     * @param array $allProductIds
     * @param array $rulesConfig
     * @param array $childIds
     * @param bool $addParents
     * @param bool $process
     * @return array
     */
    public function applyRules(
        $oldStocks,
        $newStocks,
        $allProductIds,
        $childIds,
        $rulesConfig,
        $addParents = true,
        $process = true
    ) {
        $ids = [];

        if (!$process) {
            // Get in stock products ids
            foreach ($oldStocks as $productId => $stockData) {
                $stockStatus = 0;
                if (isset($stockData['innv_ci_stock_status']) && $stockData['innv_ci_stock_status']) {
                    $stockStatus = 1;
                }
                if ($stockStatus) {
                    $ids[] = $productId;
                }
            }

            if ($addParents) {
                $ids = $this->extendWithParentIds($ids, $allProductIds, $childIds);
            }
            return $ids;
        }

        // Get product ids not to be cache cleaned
        $this->validator->setConfig($rulesConfig);
        $ids = $this->validator->validate($newStocks, $oldStocks);

        if ($addParents) {
            $ids = $this->extendWithParentIds($ids, $allProductIds, $childIds);
        }
        return $ids;
    }

    /**
     * Add affected parent product ids
     *
     * @param $productIds
     * @param array $allProductIds
     * @param array $childIds
     * @return array
     */
    public function extendWithParentIds($productIds, $allProductIds, $childIds)
    {
        // Get only child product ids not affected by "don't clean cache"
        $notAffectedChildIds = array_intersect(
            array_keys($childIds),
            array_diff($allProductIds, $productIds)
        );

        // Get parent product ids with children not affected
        $notAffectedParentIds = [];
        foreach ($notAffectedChildIds as $childId) {
            $notAffectedParentIds[] = $childIds[$childId];
        }
        $notAffectedParentIds = array_unique($notAffectedParentIds);

        $ids = $productIds;
        foreach ($productIds as $productId) {
            // Remove parent product from affected if any child is not affected
            if (($key = array_search($productId, $notAffectedParentIds)) !== false) {
                unset($ids[$key]);
                continue;
            }

            if (isset($childIds[$productId])) {
                // Add parent product id if it doesn't belong to not affected
                if (
                    array_search($childIds[$productId], $ids) === false &&
                    array_search($childIds[$productId], $notAffectedParentIds) === false
                ) {
                    $ids[] = $childIds[$productId];
                }
            }
        }
        $ids = array_unique($ids);
        return $ids;
    }
}
