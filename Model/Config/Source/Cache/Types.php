<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model\Config\Source\Cache;

/**
 * Cache types source
 */
class Types implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList)
    {
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            foreach ($this->cacheTypeList->getTypeLabels() as $type => $label) {
                $this->options[$type] = $label;
            }
        }
        $options = [];
        if (!empty($this->options)) {
            foreach ($this->options as $type => $label) {
                $options[] = ['value' => $type, 'label' => __($label)];
            }
        }
        return $options;
    }
}
