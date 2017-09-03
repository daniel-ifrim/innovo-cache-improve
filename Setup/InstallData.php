<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Setup;

use Magento\Framework\Setup;
use Magento\Framework\App\Config\ScopeConfigInterface;

class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Innovo\CacheImprove\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Innovo\CacheImprove\Helper\Data $helper
    ) {
        $this->configWriter = $configWriter;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        if (!$this->helper->isMagentoVersion20()) {
            $configPath = \Innovo\CacheImprove\Helper\Data::XML_PATH_EXTEND_FORCE_FPC_ADD_PRODUCT_TAGS;
            $this->configWriter->save(
                $configPath,
                0,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );

            $configPath = \Innovo\CacheImprove\Helper\Topmenu::XML_PATH_APPLY_IMPROVE;
            $this->configWriter->save(
                $configPath,
                0,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
    }
}
