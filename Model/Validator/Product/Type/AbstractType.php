<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Validator\Product\Type;

/**
 * Class Validator Product Type Abstract
 */
class AbstractType
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $newStockData
     * @param array $oldStockData
     * @return bool
     */
    public function validate($newStockData, $oldStockData)
    {
        return false;
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

    /**
     * Get config by rule name
     *
     * @param $name
     * @return mixed
     */
    public function getConfigByRuleName($name)
    {
        if (is_array($this->config) && isset($this->config[$name])) {
            return $this->config[$name];
        }
        return $name;
    }
}
