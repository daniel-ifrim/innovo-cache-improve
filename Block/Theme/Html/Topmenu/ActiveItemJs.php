<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Block\Theme\Html\Topmenu;

/**
 *  Class ActiveItemJs block
 */
class ActiveItemJs extends \Magento\Framework\View\Element\Template
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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Innovo\CacheImprove\Helper\Data $helper
     * @param \Innovo\CacheImprove\Helper\Topmenu $topmenuHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Innovo\CacheImprove\Helper\Data $helper,
        \Innovo\CacheImprove\Helper\Topmenu $topmenuHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->topmenuHelper = $topmenuHelper;
        parent::__construct($context, $data);
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

    /**
     * Get js selector
     *
     * @return array
     */
    public function getJsSelector()
    {
        return $this->topmenuHelper->getJsSelector();
    }

    /**
     * Get js parents selector
     *
     * @return array
     */
    public function getJsParentsSelector()
    {
        return $this->topmenuHelper->getJsParentsSelector();
    }

    /**
     * Get active css class
     *
     * @return string
     */
    public function getCssActive()
    {
        return $this->topmenuHelper->getCssActive();
    }

    /**
     * Get active css class of parent items
     *
     * @return string
     */
    public function getCssHasActive()
    {
        return $this->topmenuHelper->getCssHasActive();
    }
}
