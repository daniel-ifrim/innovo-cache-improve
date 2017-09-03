<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Helper;

/**
 * CacheImprove stock helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Stock extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_QTY_DIVISION = 'innovo_cacheimprove/stock/qty_division';
    const XML_PATH_DISCRETE_QTY = 'innovo_cacheimprove/stock/discrete_qty';

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
     * Get quantity division configuration
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getQtyDivision()
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_QTY_DIVISION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get discrete quantity configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getDiscreteQty()
    {
        $str = $this->scopeConfig->getValue(
            self::XML_PATH_DISCRETE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $numbers = [];
        $discrete = explode(",", $str);
        foreach ($discrete as $interval) {
            $arr = explode("-", $interval);
            if (count($arr) == 1) {
                $numbers[] = ['eq' => $arr[0]];
            } elseif (count($arr) == 2) {
                $numbers[] = ['min' => $arr[0], 'max' => $arr[1]];
            }
        }
        return $numbers;
    }
}
