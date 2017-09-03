<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Helper;

use Magento\Framework\App\ProductMetadataInterface as AppProductMetadataInterface;

/**
 * CacheImprove default helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GENERAL_ACTIVE = 'innovo_cacheimprove/general/active';
    const XML_PATH_EXTEND_FPC_CACHE_TYPE_CLEAN_TAGS = 'innovo_cacheimprove/extend/fpc_cache_type_clean_tags';
    const XML_PATH_EXTEND_FORCE_FPC_ADD_PRODUCT_TAGS = 'innovo_cacheimprove/extend/force_fpc_add_product_tags';
    const XML_PATH_EXTEND_FPC_ADD_PARENT_PRODUCT_TAGS = 'innovo_cacheimprove/extend/fpc_add_parent_product_tags';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AppProductMetadataInterface
     */
    protected $appProductMetadata;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AppProductMetadataInterface $appProductMetadata
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AppProductMetadataInterface $appProductMetadata
    ) {
        $this->storeManager = $storeManager;
        $this->appProductMetadata = $appProductMetadata;
        parent::__construct($context);
    }

    /**
     * Check if extension is active.
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isActive()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERAL_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * Get additional cache types to clean by tags after page cache clean by tags
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getFpcCacheTypeCleanTags()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_EXTEND_FPC_CACHE_TYPE_CLEAN_TAGS,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES
        );
        $tmp = explode(",", $str);
        $arr = [];
        if (!empty($tmp)) {
            foreach ($tmp as $val) {
                $val = trim($val);
                if (!empty($val)) {
                    $arr[] = $val;
                }
            }
        }
        return $arr;
    }

    /**
     * Flag to force to add product tags on page cache clean by tags.
     * Magento 2.0.x backward compatibility.
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isForceFpcAddProductTags()
    {
        $bool = $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXTEND_FORCE_FPC_ADD_PRODUCT_TAGS,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES
        );
        if (!($bool && $this->isMagentoVersion20())) {
            return false;
        }
        return $bool;
    }

    /**
     * Flag to force to add parent product tags on page cache clean by tags.
     * Magento >= 2.1.3 compatibility.
     *
     * @return boolean
     * @codeCoverageIgnore
     */
    public function isFpcAddParentProductTags()
    {
        $bool = $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXTEND_FPC_ADD_PARENT_PRODUCT_TAGS,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES
        );
        return $bool;
    }

    /**
     * Set session allow flag
     *
     * @param $bool
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return \Innovo\CacheImprove\Helper\Data
     */
    public function setInnovoCiAllowFlag($bool, $session)
    {
        if (is_object($session) && ($session instanceof \Magento\Framework\Session\SessionManagerInterface)) {
            $session->setInnovoCiAllowFlag($bool);
        }
        return $this;
    }

    /**
     * Get session allow flag
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return bool
     */
    public function getInnovoCiAllowFlag($session)
    {
        if (is_object($session) && ($session instanceof \Magento\Framework\Session\SessionManagerInterface)) {
            return (bool) $session->getInnovoCiAllowFlag();
        }
        return false;
    }

    /**
     * Unset session allow flag
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @return \Innovo\CacheImprove\Helper\Data
     */
    public function unsInnovoCiAllowFlag($session)
    {
        if (is_object($session) && ($session instanceof \Magento\Framework\Session\SessionManagerInterface)) {
            $session->unsInnovoCiAllowFlag();
        }
        return $this;
    }

    /**
     * Get cache improve allow flag
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param bool $clear
     * @return bool
     */
    public function getCiAllow($session, $clear = false)
    {
        if ($this->isActive() && $this->getInnovoCiAllowFlag($session)) {
            if ($clear) {
                $this->unsInnovoCiAllowFlag($session);
            }
            return true;
        }
        return false;
    }

    /**
     * Flag to stock indexer has dependency on category ids
     *
     * @return bool
     */
    public function isStockIndexerCacheAffectedByCategories()
    {
        return $this->isMagentoVersion20();
    }

    /**
     * Is Magento 2.0.x version
     *
     * @return bool
     */
    public function isMagentoVersion20()
    {
        $version = $this->appProductMetadata->getVersion();
        $versionParts = explode('.', $version);
        if (!isset($versionParts[0]) || !isset($versionParts[1])) {
            return false;
        }
        if (version_compare($version, '2.1.0', '<')) {
            return true;
        }
        return false;
    }

    /**
     * Compare Magento version
     *
     * @param string $compareVersion
     * @param string $sign
     * @return bool
     */
    public function isMagentoVersion($compareVersion, $sign)
    {
        $version = $this->appProductMetadata->getVersion();
        $versionParts = explode('.', $version);
        if (!isset($versionParts[0]) || !isset($versionParts[1])) {
            return false;
        }
        if (version_compare($version, $compareVersion, $sign)) {
            return true;
        }
        return false;
    }
}
