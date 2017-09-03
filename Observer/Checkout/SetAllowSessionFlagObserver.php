<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Innovo\CacheImprove\Helper\Data as CacheHelper;

/**
 * Class SetAllowSessionFlagObserver
 * Set session checkout allow flag on checkout_index_index route.
 */
class SetAllowSessionFlagObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var CacheHelper
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param CacheHelper $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        CacheHelper $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isActive()) {
            return;
        }

        try {
            $this->helper->setInnovoCiAllowFlag(true, $this->session);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
