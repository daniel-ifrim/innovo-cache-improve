<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->updateConfigFpcAddParentProductTags();
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->updateConfigFpcCacheTypeCleanTags();
        }

        $setup->endSetup();
    }

    protected function updateConfigFpcAddParentProductTags()
    {
        if ($this->helper->isMagentoVersion('2.1.3', '>=')) {
            $configPath = \Innovo\CacheImprove\Helper\Data::XML_PATH_EXTEND_FPC_ADD_PARENT_PRODUCT_TAGS;
            $this->configWriter->save(
                $configPath,
                1,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
    }

    protected function updateConfigFpcCacheTypeCleanTags()
    {
        if ($this->helper->isMagentoVersion('2.1.0', '>=')) {
            $configPath = \Innovo\CacheImprove\Helper\Data::XML_PATH_EXTEND_FPC_CACHE_TYPE_CLEAN_TAGS;
            $this->configWriter->save(
                $configPath,
                'block_html,collections',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
        }
    }
}
