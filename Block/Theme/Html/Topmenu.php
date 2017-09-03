<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Block\Theme\Html;

/**
 * Class Topmenu block
 * Add category id as CSS class
 */
class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * @var bool
     */
    protected $isImproveActive;

    /**
     * {@inheritdoc}
     * Add category id as CSS class
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = parent::_getMenuItemClasses($item);

        //if (!$this->isImproveActive()) {
        //    return $classes;
        //}

        // $child->getId() is category-node-123
        $classes[] = 'innv-' . $item->getId();

        return $classes;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setModuleName(
            $this->extractModuleName('Magento\Theme\Block\Html\Topmenu')
        );
        return parent::_toHtml();
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isImproveActive()
    {
        if ($this->isImproveActive === null) {
            $helper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Innovo\CacheImprove\Helper\Data::class);
            $topmenuHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Innovo\CacheImprove\Helper\Topmenu::class);

            $this->isImproveActive = false;
            if (
                $helper->isActive() &&
                $topmenuHelper->isApplyImprove() &&
                $topmenuHelper->isStaticCache() &&
                count($topmenuHelper->getJsSelector())
            ) {
                $this->isImproveActive = true;
            }
        }
        return $this->isImproveActive;
    }
}
