<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;
use Innovo\CacheImprove\Helper\Data as CacheHelper;
use Innovo\CacheImprove\Helper\Topmenu as TopmenuHelper;

/**
 * Observer class RemoveActiveItem
 * Remove active category in Magento 2.0.x
 *
 * @see \Magento\Catalog\Observer\AddCatalogToTopmenuItemsObserver
 */
class TopmenuRemoveActiveItem implements ObserverInterface
{
    /**
     * @var CacheHelper
     */
    protected $helper;

    /**
     * @var TopmenuHelper
     */
    protected $topmenuHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param CacheHelper $helper
     * @param TopmenuHelper $topmenuHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CacheHelper $helper,
        TopmenuHelper $topmenuHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->topmenuHelper = $topmenuHelper;
        $this->logger = $logger;
    }

    /**
     * Remove active category from top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!(
            $this->helper->isActive() &&
            $this->topmenuHelper->isApplyImprove() &&
            $this->topmenuHelper->isStaticCache()
        )) {
            return;
        }

        try {
            /** @var \Magento\Framework\Data\Tree\Node $menu */
            $menu = $observer->getMenu();
            $this->removeIsActiveRecursive($menu);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Remove active item and has active recursively
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     */
    public function removeIsActiveRecursive(\Magento\Framework\Data\Tree\Node $item)
    {
        if ($item->getIsActive()) {
            $item->setIsActive(false);
        }
        if ($item->getHasActive()) {
            $item->setHasActive(false);
        }

        $children = $item->getChildren();
        if ($children->count()) {
            foreach ($children as $child) {
                $this->removeIsActiveRecursive($child);
            }
        }
    }
}
