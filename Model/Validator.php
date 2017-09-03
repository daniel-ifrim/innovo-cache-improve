<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

/**
 * Class Validator
 */
class Validator
{
    /**
     * @var Validator\Pool
     */
    protected $validators;

    /**
     * @var array
     */
    protected $config = [];

    public function __construct(
        Validator\Pool $validators
    ) {
        $this->validators = $validators;
    }

    /**
     * @param array $newStocks
     * @param array $oldStocks
     * @return array
     */
    public function validate($newStocks, $oldStocks)
    {
        $ids = [];
        foreach ($newStocks as $productId => $newStockData) {
            $oldStockData = [];
            if (isset($oldStocks[$productId]) && is_array($oldStocks[$productId])) {
                $oldStockData = $oldStocks[$productId];
            }

            $type = 'abstract';
            if (isset($newStockData['type_id']) && $newStockData['type_id']) {
                $type = $newStockData['type_id'];
            }
            $validators = $this->validators->getValidators($type);

            $all = true;
            /** @var Validator\Product\Type\AbstractType $validator */
            foreach ($validators as $validator) {
                $validator->setConfig($this->getConfig());
                if (!$validator->validate($newStockData, $oldStockData)) {
                    $all = false;
                    break;
                }
            }
            if ($all) {
                $ids[] = $productId;
            }
        }
        return $ids;
    }

    /**
     * Set rules config
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get rules config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
