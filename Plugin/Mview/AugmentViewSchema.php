<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Plugin\Mview;

use \Innovo\CacheImprove\Model\Changelog\ExtensionInterface as ChangelogExtensionInterface;
use \Innovo\CacheImprove\Model\View\ExtensionInterface as ViewExtensionInterface;

/**
 * Class AugmentViewSchema Plugin
 * Augment changelog backend schema
 */
class AugmentViewSchema
{
    /**
     * @var \Innovo\CacheImprove\Helper\Data
     */
    protected $helper;

    /**
     * @var ChangelogExtensionInterface
     */
    protected $changelogExtension;

    /**
     * @var ViewExtensionInterface
     */
    protected $viewExtension;

    /**
     * @var \Magento\Framework\Mview\View\CollectionFactory;
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Mview\View\CollectionInterface
     */
    protected $collection;

    /**
     * @param \Innovo\CacheImprove\Helper\Data $helper
     * @param ChangelogExtensionInterface $changelogExtension
     * @param ViewExtensionInterface $viewExtension
     * @param \Magento\Framework\Mview\View\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Innovo\CacheImprove\Helper\Data $helper,
        ChangelogExtensionInterface $changelogExtension,
        ViewExtensionInterface $viewExtension,
        \Magento\Framework\Mview\View\CollectionFactory $collectionFactory
    ) {
        $this->helper = $helper;
        $this->changelogExtension = $changelogExtension;
        $this->viewExtension = $viewExtension;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Add column innv_ci_use_cache
     *
     * @param \Magento\Framework\Mview\View $subject
     * @param \Magento\Framework\Mview\View $result
     * @return \Magento\Framework\Mview\View
     */
    public function afterSubscribe(\Magento\Framework\Mview\View $subject, \Magento\Framework\Mview\View $result)
    {
        if (!$this->helper->isActive()) {
            return $result;
        }

        $collection = $this->getCollection();
        if (!$this->viewExtension->isRelatedAffectedView($subject, $collection)) {
            return $result;
        }

        $changelog = $subject->getChangelog();
        $this->changelogExtension->addExtensionSchema($changelog);

        return $result;
    }

    /**
     * @return \Magento\Framework\Mview\View\CollectionInterface
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
        }
        return $this->collection;
    }
}
