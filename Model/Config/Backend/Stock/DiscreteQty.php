<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Config\Backend\Stock;

/**
 * DiscreteQty backend
 */
class DiscreteQty extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate before save config
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value == "") {
            return $this;
        }

        if (!preg_match('/^(?:(?:[0-9]+)|(?:[0-9]+-[0-9]+),?)+$/', $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid value of field Discrete Quantities. Enter numbers separated by comma.'.
                    'Intervals are 2 numbers separated by minus.'
                )
            );
        }
        $value = trim($value);

        $numbers = [];
        $discrete = explode(",", $value);
        foreach ($discrete as $interval) {
            $numbers[] = explode("-", $interval);
        }
        $prev = -9999;
        foreach ($numbers as $val) {
            if (count($val) == 1) {
                if ($val[0] <= 0) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Value ' . $val[0] . ' is not a positive number')
                    );
                }
                if ($prev >= $val[0]) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Numbers must be ascendant. ' .
                            $prev . ' is greater or equal than ' . $val[0]
                        )
                    );
                }
                $prev = $val[0];
            } else {
                $min = $val[0];
                $max = $val[1];
                if ($min >= $max) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Interval of numbers incorrect. ' .
                            $min . ' is greater or equal than ' . $max
                        )
                    );
                }
                if ($prev >= $min) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Numbers must be ascendant. ' .
                            $prev . ' is greater or equal than ' . $val[0]
                        )
                    );
                }
                $prev = $max;
            }
        }

        $this->setValue($value);

        return $this;
    }
}
