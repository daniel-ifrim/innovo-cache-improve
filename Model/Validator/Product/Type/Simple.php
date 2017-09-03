<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Validator\Product\Type;

/**
 * Class Validator Product Type Simple
 */
class Simple extends AbstractType
{
    /**
     * Validate product stock that does not require cache clean
     *
     * @param array $newStockData
     * @param array $oldStockData
     * @return bool
     */
    public function validate($newStockData, $oldStockData)
    {
        // Precedence on stock status
        if (!$this->validateStockStatus($newStockData, $oldStockData)) {
            return false;
        }

        // Validate quantity that do not require cache clean
        if (!$this->validateQty($newStockData, $oldStockData)) {
            return false;
        }

        return true;
    }

    /**
     * Validate by stock status to find if product does not require cache clean
     *
     * @param array $newStockData
     * @param array $oldStockData
     * @return bool
     */
    public function validateStockStatus($newStockData, $oldStockData)
    {
        $newStockStatus = 0;
        if (isset($newStockData['innv_ci_stock_status']) && $newStockData['innv_ci_stock_status']) {
            $newStockStatus = 1;
        }

        $oldStockStatus = 1;
        if (!(isset($oldStockData['innv_ci_stock_status']) && $oldStockData['innv_ci_stock_status'])) {
            $oldStockStatus = 0;
        }

        if ($newStockStatus && ($newStockStatus == $oldStockStatus)) {
            return true;
        }

        return false;
    }

    /**
     * Validate by stock quantity to find if product does not require cache clean
     *
     * @param array $newStockData
     * @param array $oldStockData
     * @return bool
     */
    public function validateQty($newStockData, $oldStockData)
    {
        $config = $this->getConfigByRuleName('qty');
        if (empty($config)) {
            return true;
        }

        if (isset($newStockData['qty']) && $newStockData['qty'] > 0) {
            if (isset($oldStockData['qty']) && $oldStockData['qty'] > 0) {
                // Quantity hasn't changed
                if ($newStockData['qty'] == $oldStockData['qty']) {
                    return true;
                }
            }
        }

        // Get rules configurations
        $qtyDivision = 1;
        $discreteQty = [];
        if (is_array($config)) {
            if (isset($config['qty_division'])) {
                $qtyDivision = $config['qty_division'];
            }
            if (isset($config['discrete_qty']) && is_array($config['discrete_qty'])) {
                $discreteQty = $config['discrete_qty'];
            }
            if (!($qtyDivision > 1 || !empty($discreteQty))) {
                return true;
            }
        } else {
            return true;
        }

        $qty = 0;
        if (isset($newStockData['qty'])) {
            $qty = $newStockData['qty'];
        }

        // Can't compare product with qty 0
        if ($qty <= 0) {
            return true;
        }

        // New Stock quantity divides with the rule's number
        if ($qtyDivision > 1 && $qty % $qtyDivision == 0) {
            return false;
        }

        foreach ($discreteQty as $interval) {
            if (count($interval) == 1) {
                reset($interval);
                $compareQty = current($interval);
                // New stock quantity matches the rule's number
                if ($compareQty > 0 && $qty == $compareQty) {
                    return false;
                }
            } elseif (count($interval) == 2) {
                // New stock quantity is in the rule's interval
                $min = $interval['min'];
                $max = $interval['max'];
                if ($min <= $qty && $max > $qty) {
                    return false;
                }
            }
        }

        return true;
    }
}
