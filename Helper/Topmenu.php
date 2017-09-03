<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Helper;

use Magento\Framework\App\ProductMetadataInterface as AppProductMetadataInterface;

/**
 * CacheImprove topmenu helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Topmenu extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_APPLY_IMPROVE = 'innovo_cacheimprove/topmenu/apply_improve';
    const XML_PATH_BLOCK_NAME = 'innovo_cacheimprove/topmenu/block_name';

    const XML_PATH_REMOVE_ESI_POLICY = 'innovo_cacheimprove/topmenu/remove_esi_policy';
    const XML_PATH_OVERRIDE_TTL = 'innovo_cacheimprove/topmenu/override_ttl';
    const XML_PATH_TTL = 'innovo_cacheimprove/topmenu/ttl';

    const XML_PATH_STATIC_CACHE = 'innovo_cacheimprove/topmenu/static_cache';
    const XML_PATH_CACHE_LIFETIME = 'innovo_cacheimprove/topmenu/cache_lifetime';

    const XML_PATH_JS_SELECTOR = 'innovo_cacheimprove/topmenu/js_selector';
    const XML_PATH_JS_PARENTS_SELECTOR = 'innovo_cacheimprove/topmenu/js_parents_selector';
    const XML_PATH_CSS_ACTIVE = 'innovo_cacheimprove/topmenu/css_active';
    const XML_PATH_CSS_HAS_ACTIVE = 'innovo_cacheimprove/topmenu/css_has_active';

    const TOPMENU_CACHE_TAG = 'innv_topmenu';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Remove top menu ESI policy flag
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isApplyImprove()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_APPLY_IMPROVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get block name(s) of top menu(s)
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getBlockNames()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_BLOCK_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $str = strtr(
            $str,
            [
                "\n" => ",",
                "\r" => ",",
                " " => ",",
            ]
        );
        $str = preg_replace('/,,+/', ',', $str);
        $str = trim(trim(trim($str), ","));
        if (empty($str) || str_replace(",", "", $str) == "") {
            $str = 'catalog.topnav';
        }
        $value = explode(",", $str);
        if (empty($value)) {
            $value = ['catalog.topnav'];
        }
        return $value;
    }

    /**
     * Remove top menu ESI policy flag
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isRemoveEsi()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REMOVE_ESI_POLICY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Override top menu ESI policy TTL flag
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isOverrideTtl()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_OVERRIDE_TTL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Overridden TTL of top menu ESI policy
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getTtl()
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_TTL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$value) {
            $value = 3600;
        }
        return $value;
    }

    /**
     * Flag to decouple top menu cache from categories cache tags
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isStaticCache()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_STATIC_CACHE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get top menu cache lifetime
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getCacheLifetime()
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_CACHE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$value) {
            $value = 3600;
        }
        return $value;
    }

    /**
     * Get js selector
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getJsSelector()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_JS_SELECTOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->getConfigSelectorsAsArray($str);
    }

    /**
     * Get js parents selector
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getJsParentsSelector()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_JS_PARENTS_SELECTOR,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->getConfigSelectorsAsArray($str);
    }

    /**
     * Get active css class
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCssActive()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_CSS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $str = trim($str);
        if (empty($str)) {
            return '';
        }
        return $str;
    }

    /**
     * Get active css class of parent items
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCssHasActive()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_CSS_HAS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $str = trim($str);
        if (empty($str)) {
            return '';
        }
        return $str;
    }

    /**
     * Get selectors from config value as array
     *
     * @param string $str
     * @return array
     */
    protected function getConfigSelectorsAsArray($str)
    {
        $str = strtr(
            $str,
            [
                "\n" => "|",
                "\r" => "|",
            ]
        );
        $str = preg_replace('/\|\|+/', '|', $str);
        $str = trim(trim(trim($str), "|"));
        if (empty($str) || str_replace("|", "", $str) == "") {
            return [];
        }
        $value = explode("|", $str);
        if (empty($value)) {
            return [];
        }
        return $value;
    }

    /**
     * Get top menu cache tag
     *
     * @return string
     */
    public function getTopmenuCacheTag()
    {
        return self::TOPMENU_CACHE_TAG;
    }
}
