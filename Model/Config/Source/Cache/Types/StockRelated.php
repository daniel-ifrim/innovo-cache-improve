<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Cache types source
 */
namespace Innovo\CacheImprove\Model\Config\Source\Cache\Types;

class StockRelated extends \Innovo\CacheImprove\Model\Config\Source\Cache\Types
{
    /**
     * Disallowed cache types
     *
     * @var array
     */
    protected $disallow = [];

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param array $disallow
     */
    public function __construct(\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, $disallow)
    {
        $this->disallow = $disallow;
        parent::__construct($cacheTypeList);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        parent::toOptionArray();

        $opts = $this->options;
        if (is_array($this->disallow) && !empty($this->disallow)) {
            foreach ($this->disallow as $type) {
                if (isset($this->options[$type])) {
                    unset($opts[$type]);
                }
            }
        }
        $options = [];
        if (!empty($opts)) {
            foreach ($opts as $type => $label) {
                $options[] = ['value' => $type, 'label' => __($label)];
            }
        }
        return $options;
    }
}
