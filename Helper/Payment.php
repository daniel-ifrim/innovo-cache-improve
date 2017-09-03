<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Helper;

use Magento\Framework\App\ProductMetadataInterface as AppProductMetadataInterface;

/**
 * CacheImprove payment helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_FULL_ACTION_NAMES_PRE_DISPATCH = 'innovo_cacheimprove/payment/full_action_names_pre_dispatch';
    const XML_PATH_FULL_ACTION_NAMES_POST_DISPATCH = 'innovo_cacheimprove/payment/full_action_names_post_dispatch';

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
     * Get allowed full action names hooked on action pre dispatch event
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getFullActionNamesPreDispatch()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_FULL_ACTION_NAMES_PRE_DISPATCH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->getConfigFullActionAsArray($str);
    }

    /**
     * Get allowed full action names hooked on action post dispatch event
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getFullActionNamesPostDispatch()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_FULL_ACTION_NAMES_POST_DISPATCH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->getConfigFullActionAsArray($str);
    }

    /**
     * Get full action names from config value as array
     *
     * @param string $str
     * @return array
     */
    protected function getConfigFullActionAsArray($str)
    {
        $str = strtolower($str);
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
}
