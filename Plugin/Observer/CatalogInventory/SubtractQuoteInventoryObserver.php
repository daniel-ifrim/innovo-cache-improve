<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Plugin\Observer\CatalogInventory;

use Magento\Framework\Event\Observer as EventObserver;
use Innovo\CacheImprove\Helper\Data as CacheHelper;
use Innovo\CacheImprove\Helper\Stock as CacheStockHelper;
use Innovo\CacheImprove\Model\StockRegistryProvider as StockRegistryProviderCache;
use Innovo\CacheImprove\Model\StockProvider as StockProviderCache;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockIndexerProcessor;
use Innovo\CacheImprove\Model\View\Extension as ViewExtension;
use Innovo\CacheImprove\Model\RulesApplier as RulesApplierCache;
use Innovo\CacheImprove\Model\AffectedProductsContext;

/**
 * Mark not to clean cache to MView changelog tables
 *
 * @see \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver
 * @see \Magento\CatalogInventory\Model\StockManagement::registerProductsSale()
 */
class SubtractQuoteInventoryObserver
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var StockIndexerProcessor
     */
    protected $stockIndexerProcessor;

    /**
     * @var \Magento\CatalogInventory\Observer\ProductQty
     */
    protected $productQty;

    /**
     * @var StockRegistryProviderCache
     */
    protected $stockRegistryProviderCache;

    /**
     * @var StockProviderCache
     */
    protected $stockProviderCache;

    /**
     * @var ViewExtension
     */
    protected $viewExtension;

    /**
     * @var RulesApplierCache
     */
    protected $rulesApplierCache;

    /**
     * @var AffectedProductsContext
     */
    protected $affectedProductsContext;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var CacheHelper
     */
    protected $helper;

    /**
     * @var CacheStockHelper
     */
    protected $stockHelper;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param StockIndexerProcessor $stockIndexerProcessor
     * @param \Magento\CatalogInventory\Observer\ProductQty $productQty
     * @param StockRegistryProviderCache $stockRegistryProviderCache
     * @param StockProviderCache $stockProviderCache
     * @param ViewExtension $viewExtension
     * @param RulesApplierCache $rulesApplierCache
     * @param AffectedProductsContext $affectedProductsContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param CacheHelper $helper
     * @param CacheStockHelper $stockHelper
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        StockIndexerProcessor $stockIndexerProcessor,
        \Magento\CatalogInventory\Observer\ProductQty $productQty,
        StockRegistryProviderCache $stockRegistryProviderCache,
        StockProviderCache $stockProviderCache,
        ViewExtension $viewExtension,
        RulesApplierCache $rulesApplierCache,
        AffectedProductsContext $affectedProductsContext,
        \Psr\Log\LoggerInterface $logger,
        CacheHelper $helper,
        CacheStockHelper $stockHelper
    ) {
        $this->session = $session;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->productQty = $productQty;
        $this->stockRegistryProviderCache = $stockRegistryProviderCache;
        $this->stockProviderCache = $stockProviderCache;
        $this->viewExtension = $viewExtension;
        $this->rulesApplierCache = $rulesApplierCache;
        $this->affectedProductsContext = $affectedProductsContext;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->stockHelper = $stockHelper;
    }

    /**
     * - Retrieve product ids that went out of stock after subtract quantity.
     * - Mview: Mark changelog entries to not clean cache unless is required.
     *
     * @param \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver $subject
     * @param \Closure $proceed
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver $subject,
        \Closure $proceed,
        EventObserver $observer
    ) {
        if (!$this->helper->getCiAllow($this->session)) {
            $proceed($observer);
            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $initialAffectedProductStocks = [];
        $oldVersionsIds = [];
        $isProcessInventory = true;
        if ($quote->getInventoryProcessed()) {
            $isProcessInventory = false;
        }
        try {
            // Get initial stock data of products affected by 'don't clean cache'
            $items = $this->productQty->getProductQty($quote->getAllItems());
            if ($isProcessInventory) {
                $initialAffectedProductStocks = $this->stockRegistryProviderCache
                    ->getAffectedProductsStocks($items);
            } else {
                $initialAffectedProductStocks = $this->stockProviderCache
                    ->getAffectedProductsStocks($items, null, true);
            }

            // Get views version ids before subtract quantities
            if ($this->stockIndexerProcessor->isIndexerScheduled()) {
                $view = $this->stockIndexerProcessor->getIndexer()->getView();
                $oldVersionsIds = $this->viewExtension->getAllVersionsIdsByView($view);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            $proceed($observer);
            return;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $proceed($observer);
            return;
        }

        $proceed($observer);

        $currentVersionsIds = [];
        try {
            $items = $this->productQty->getProductQty($quote->getAllItems());
            if ($isProcessInventory) {
                $affectedProductStocks = $this->stockProviderCache
                    ->getAffectedProductsStocks($items, null, true);
            } else {
                $affectedProductStocks = $initialAffectedProductStocks;
            }

            if ($this->stockIndexerProcessor->isIndexerScheduled()) {
                $view = $this->stockIndexerProcessor->getIndexer()->getView();
                /** @var \Magento\Framework\Mview\View\Collection $collection */
                $collection = null;
                $currentVersionsIds = $this->viewExtension->getAllVersionsIdsByView($view, true, $collection);
            }

            $rulesConfig = ['qty' => [
                'qty_division' => $this->stockHelper->getQtyDivision(),
                'discrete_qty' => $this->stockHelper->getDiscreteQty()
            ]];
            // Get product ids that do not require clean cache
            $affectedProductIds = $this->rulesApplierCache->applyRules(
                $initialAffectedProductStocks,
                $affectedProductStocks,
                array_keys($items),
                $this->getProductsChildIds($quote->getAllItems()),
                $rulesConfig,
                true,
                $isProcessInventory
            );

            // Memorize affected product ids
            $this->affectedProductsContext->registerEntities($affectedProductIds);

            if ($this->stockIndexerProcessor->isIndexerScheduled()) {
                // Mark products that do not require clean cache
                $this->viewExtension->updateUseCache(
                    $view,
                    $affectedProductIds,
                    $oldVersionsIds,
                    $currentVersionsIds,
                    true,
                    $collection
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @return array
     */
    public function getProductsChildIds($items)
    {
        $childIds = [];
        foreach ($items as $item) {
            $children = $item->getChildrenItems();
            if ($children) {
                /** @var \Magento\Quote\Model\Quote\Item $childItem */
                foreach ($children as $childItem) {
                    $childIds[$childItem->getProductId()] = $item->getProductId();
                }
            }
            if ($item->getParentItemId()) {
                $childIds[$item->getProductId()] = $item->getParentItem()->getProductId();
            }
        }
        return $childIds;
    }
}
