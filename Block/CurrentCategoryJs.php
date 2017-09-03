<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Block;

/**
 *  Class CurrentCategoryJs block
 */
class CurrentCategoryJs extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Innovo\CacheImprove\Helper\Data
     */
    protected $helper;

    /**
     * @var \Innovo\CacheImprove\Helper\Topmenu
     */
    protected $topmenuHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Innovo\CacheImprove\Helper\Data $helper
     * @param \Innovo\CacheImprove\Helper\Topmenu $topmenuHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Innovo\CacheImprove\Helper\Data $helper,
        \Innovo\CacheImprove\Helper\Topmenu $topmenuHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->topmenuHelper = $topmenuHelper;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get current category id
     *
     * @return int|null
     */
    public function getCurrentCategoryId()
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->coreRegistry
            ->registry('current_category');
        if (
            $category instanceof \Magento\Catalog\Model\Category &&
            $category->getEntityId() > 0
        ) {
            return $category->getEntityId();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!(
            $this->helper->isActive() &&
            $this->topmenuHelper->isApplyImprove() &&
            $this->topmenuHelper->isStaticCache() &&
            count($this->topmenuHelper->getJsSelector())
        )) {
            return '';
        }
        return parent::_toHtml();
    }
}
