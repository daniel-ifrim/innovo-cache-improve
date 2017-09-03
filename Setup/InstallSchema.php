<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Innovo\CacheImprove\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Mview\View\CollectionFactory;
use \Magento\Framework\DB\Ddl\Table;
use \Innovo\CacheImprove\Model\View\ExtensionInterface as ViewExtensionInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ViewExtensionInterface
     */
    protected $viewExtension;

    /**
     * Init
     *
     * @param CollectionFactory $collectionFactory
     * @param ViewExtensionInterface $viewExtension
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ViewExtensionInterface $viewExtension
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->viewExtension = $viewExtension;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $connection = $setup->getConnection();

        /** @var  \Magento\Framework\Mview\View\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->loadData();

        $affectedViews = [];
        /** @var \Magento\Framework\Mview\View $view */
        foreach ($collection as $view) {
            if (array_search($view->getId(), $this->viewExtension->getAffectedViewsIds())) {
                $affectedViews[] = $view;
            }
        }

        $relatedChangelogNames = $this->viewExtension->getRelatedChangelogNamesByViews($affectedViews, $collection);
        foreach ($collection as $view) {
            /** @var \Magento\Framework\Mview\View\Changelog $changelog */
            $changelog = $view->getChangelog();

            $changelogTableName = $setup->getTable($changelog->getName());
            if (
                array_search($changelog->getName(), $relatedChangelogNames) !== false &&
                $connection->isTableExists($changelogTableName) &&
                !$connection->tableColumnExists($changelogTableName, 'innv_ci_use_cache')
            ) {
                $connection->addColumn(
                    $setup->getTable($changelogTableName),
                    'innv_ci_use_cache',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Use Cache',
                    ]
                );
            }
        }

        $installer->endSetup();
    }
}
