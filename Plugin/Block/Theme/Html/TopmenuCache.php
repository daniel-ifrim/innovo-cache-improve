<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Plugin\Block\Theme\Html;

use Innovo\CacheImprove\Helper\Data as CacheHelper;
use Innovo\CacheImprove\Helper\Topmenu as TopmenuHelper;

/**
 * TopmenuCache Plugin
 *
 * - Remove ESI policy from Topmenu
 * - Alter TTL
 * - Alter cache tags, cache keys and cache lifetime
 * - Alter identities
 */
class TopmenuCache
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Topmenu constructor.
     * @param CacheHelper $helper
     * @param TopmenuHelper $topmenuHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CacheHelper $helper,
        TopmenuHelper $topmenuHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->topmenuHelper = $topmenuHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Around get data
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param \Closure $proceed
     * @param string|null $key
     * @param string|null $index
     * @return mixed|null
     */
    public function aroundGetData(
        \Magento\Theme\Block\Html\Topmenu $subject,
        \Closure $proceed,
        $key = null,
        $index = null
    ) {
        if (!$this->isActive($subject)) {
            return $proceed($key, $index);
        }
        if ($key == 'ttl') {
            if ($this->topmenuHelper->isRemoveEsi()) {
                // No TTL
                return null;
            }
            if (($ttl = $this->getTtl()) !== null) {
                return $ttl;
            }
        }

        if ($key == 'cache_lifetime') {
            if ($this->topmenuHelper->isStaticCache()) {
                if (($lifetime = $this->getCacheLifetime()) !== null) {
                    return $lifetime;
                }
            }
        }

        if ($key == 'cache_tags') {
            if ($this->topmenuHelper->isStaticCache()) {
                $tags = $this->getCacheTags($subject);
                if (!empty($tags)) {
                    return $tags;
                }
            }
        }

        return $proceed($key, $index);
    }

    /**
     * Get TTL
     *
     * @return int|null
     */
    public function getTtl()
    {
        if (!$this->topmenuHelper->isOverrideTtl()) {
            return null;
        }
        if (($ttl = $this->topmenuHelper->getTtl()) > 0) {
            return $ttl;
        }
        return null;
    }

    /**
     * Get cache lifetime
     *
     * @return int|null
     */
    public function getCacheLifetime()
    {
        if (($lifetime = $this->topmenuHelper->getCacheLifetime()) > 0) {
            return $lifetime;
        }
        return null;
    }

    /**
     * Around get cache lifetime
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param \Closure $proceed
     * @return int|null
     */
    public function aroundGetCacheLifetime(
        \Magento\Theme\Block\Html\Topmenu $subject,
        \Closure $proceed
    ) {
        if (!$this->isActive($subject)) {
            return $proceed();
        }

        if ($this->topmenuHelper->isStaticCache()) {
            if (($lifetime = $this->getCacheLifetime()) !== null) {
                return $lifetime;
            }
        }

        return $proceed();
    }

    /**
     * Around get cache key info
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetCacheKeyInfo(
        \Magento\Theme\Block\Html\Topmenu $subject,
        \Closure $proceed
    ) {
        if (!$this->isActive($subject)) {
            return $proceed();
        }

        if ($this->topmenuHelper->isStaticCache()) {
            return $this->getCacheKeyInfo($subject);
        }

        return $proceed();
    }

    /**
     * Get cache key info
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @return array
     */
    public function getCacheKeyInfo($subject)
    {
        return [
            'BLOCK_TPL',
            $this->storeManager->getStore()->getCode(),
            'base_url' => $subject->getBaseUrl(),
            'template' => $subject->getTemplate(),
            $this->topmenuHelper->getTopmenuCacheTag(),
            $subject->getNameInLayout(),
            $subject->getRequest()->isSecure() ? '1' : '0'
        ];
    }

    /**
     * Around get cache tags
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetCacheTags(
        \Magento\Theme\Block\Html\Topmenu $subject,
        \Closure $proceed
    ) {
        if (!$this->isActive($subject)) {
            return $proceed();
        }

        if ($this->topmenuHelper->isStaticCache()) {
            return $this->getCacheTags($subject);
        }

        return $proceed();
    }

    /**
     * Get cache tags
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @return array
     */
    public function getCacheTags($subject)
    {
        $tags = [
            \Magento\Framework\View\Element\AbstractBlock::CACHE_GROUP,
        ];
        $tags = array_unique(array_merge($tags, $this->getIdentities($subject)));
        return $tags;
    }

    /**
     * Around get identities
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetIdentities(
        \Magento\Theme\Block\Html\Topmenu $subject,
        \Closure $proceed
    ) {
        if (!$this->isActive($subject)) {
            return $proceed();
        }

        if ($this->topmenuHelper->isStaticCache()) {
            return $this->getIdentities($subject);
        }

        return $proceed();
    }

    /**
     * Get identities
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdentities($subject)
    {
        return [$this->topmenuHelper->getTopmenuCacheTag()];
    }

    /**
     * Around has data
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param \Closure $proceed
     * @param string|null $key
     * @return bool
     */
    public function aroundHasData(
        \Magento\Theme\Block\Html\Topmenu $subject,
        \Closure $proceed,
        $key = ''
    ) {
        if (!$this->isActive($subject)) {
            return $proceed($key);
        }
        if ($key == 'ttl') {
            if ($this->topmenuHelper->isRemoveEsi()) {
                // No TTL
                return false;
            }
            if (($hasTtl = $this->hasTtl()) !== null) {
                return $hasTtl;
            }
        }

        if ($key == 'cache_lifetime') {
            if ($this->topmenuHelper->isStaticCache()) {
                if (($hasLifetime = $this->hasCacheLifetime()) !== null) {
                    return $hasLifetime;
                }
            }
        }

        if ($key == 'cache_tags') {
            if ($this->topmenuHelper->isStaticCache()) {
                $tags = $this->getCacheTags($subject);
                if (!empty($tags)) {
                    return true;
                }
            }
        }

        return $proceed($key);
    }

    /**
     * Has TTL
     *
     * @return bool|null
     */
    public function hasTtl()
    {
        if (!$this->topmenuHelper->isOverrideTtl()) {
            return null;
        }
        if (($ttl = $this->topmenuHelper->getTtl()) > 0) {
            return true;
        }
        return null;
    }

    /**
     * Has cache lifetime
     *
     * @return bool|null
     */
    public function hasCacheLifetime()
    {
        if ($this->topmenuHelper->getCacheLifetime() > 0) {
            return true;
        }
        return null;
    }

    /**
     * Is Active
     *
     * @note Do not add getSomething from subject here
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @return bool
     */
    public function isActive($subject)
    {
        if (!$this->helper->isActive()) {
            return false;
        }
        if (!$this->topmenuHelper->isApplyImprove()) {
            return false;
        }
        $blockNames = $this->topmenuHelper->getBlockNames();
        if (array_search($subject->getNameInLayout(), $blockNames) === false) {
            return false;
        }
        return true;
    }
}
