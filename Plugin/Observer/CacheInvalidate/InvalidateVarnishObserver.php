<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Plugin\Observer\CacheInvalidate;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class CacheInvalidate InvalidateVarnishObserver Plugin
 */
class InvalidateVarnishObserver
{
    /**
     * @var \Innovo\CacheImprove\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Cache State
     *
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $cacheState;

    /**
     * @var \Magento\Framework\Cache\Frontend\Decorator\TagScope[]
     */
    protected $additionalCacheTypes;

    /**
     * @param \Innovo\CacheImprove\Helper\Data $helper
     * @param \Magento\PageCache\Model\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Cache\Frontend\Decorator\TagScope[] $additionalCacheTypes
     */
    public function __construct(
        \Innovo\CacheImprove\Helper\Data $helper,
        \Magento\PageCache\Model\Config $config,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        $additionalCacheTypes
    ) {
        $this->helper = $helper;
        $this->config = $config;
        $this->logger = $logger;
        $this->cacheState = $cacheState;
        $this->additionalCacheTypes = $additionalCacheTypes;
    }

    /**
     * Clean cache by tags of additional cache types
     *
     * @param \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver $subject
     * @param EventObserver $observer
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver $subject,
        EventObserver $observer
    ) {
        if (!$this->helper->isActive()) {
            return [$observer];
        }
        $cacheTypesConfig = $this->helper->getFpcCacheTypeCleanTags();
        if (empty($cacheTypesConfig)) {
            return [$observer];
        }

        try {
            if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
                /** @var \Innovo\CacheImprove\Model\CacheContext $cacheContext */
                $cacheContext = $observer->getEvent()->getObject();
                if (!($cacheContext instanceof \Innovo\CacheImprove\Model\CacheContext)) {
                    return [$observer];
                }

                foreach ($this->additionalCacheTypes as $cacheTypeInstance) {
                    $cacheType = constant(get_class($cacheTypeInstance) . '::TYPE_IDENTIFIER');
                    if (!$this->cacheState->isEnabled($cacheType)) {
                        continue;
                    }

                    $tags = $cacheContext->getExtendIdentities($cacheType);
                    if (!empty($tags)) {
                        $cacheTypeInstance->clean(
                            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                            array_unique($tags)
                        );
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [$observer];
    }
}
