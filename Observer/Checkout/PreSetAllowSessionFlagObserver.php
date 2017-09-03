<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Innovo\CacheImprove\Helper\Data as CacheHelper;
use Innovo\CacheImprove\Helper\Payment as PaymentHelper;

/**
 * Class PreSetAllowSessionFlagObserver
 * Set session checkout allow flag on all checkout configured actions.
 */
class PreSetAllowSessionFlagObserver implements ObserverInterface
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
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param CacheHelper $helper
     * @param PaymentHelper $paymentHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        CacheHelper $helper,
        PaymentHelper $paymentHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->helper = $helper;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isActive()) {
            return;
        }

        try {
            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $observer->getRequest();

            if (strcasecmp($request->getFullActionName(), "checkout_index_index") === 0) {
                return;
            }

            $allowed = $this->paymentHelper->getFullActionNamesPreDispatch();
            $name = strtolower($request->getFullActionName());
            if (array_search($name, $allowed) === false) {
                return;
            }

            $this->helper->setInnovoCiAllowFlag(true, $this->session);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
