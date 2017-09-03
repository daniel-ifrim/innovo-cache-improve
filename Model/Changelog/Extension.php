<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Innovo\CacheImprove\Model\Changelog;

use \Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Mview\View\ChangelogTableNotExistsException;
use Magento\Framework\Phrase;

/**
 * Class Changelog Extension
 *
 * @see \Magento\Framework\Mview\View\Changelog
 */
class Extension implements ExtensionInterface
{
    /**
     * Column name of changelog is persistent cacheable
     */
    const COLUMN_NAME = 'innv_ci_use_cache';

    /**
     * Database connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger;
        $this->checkConnection();
    }

    /**
     * Check DB connection
     *
     * @return void
     * @throws \Exception
     */
    protected function checkConnection()
    {
        if (!$this->connection) {
            throw new \Exception('Write DB connection is not available');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * return @void
     * @throws \Exception
     */
    public function addExtensionSchema($changelog)
    {
        try {
            $changelogTableName = $this->resource->getTableName($changelog->getName());
            if (
                $this->connection->isTableExists($changelogTableName) &&
                !$this->connection->tableColumnExists($changelogTableName, $this->getColumnName())
            ) {
                $this->connection->addColumn(
                    $changelogTableName,
                    $this->getColumnName(),
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Use Cache'
                    ]
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @param array $entityIds
     * @param array $oldVersionId
     * @param array $currentVersionId
     * @param bool $useCache
     * @return $this
     */
    public function updateUseCache($changelog, $entityIds, $oldVersionId = [], $currentVersionId = [], $useCache = true)
    {
        $useCache = $this->normalizeUseCache($useCache);

        if (empty($entityIds)) {
            return $this;
        }
        if (!is_array($entityIds)) {
            $entityIds = [$entityIds];
        }

        $changelogTableName = $this->resource->getTableName($changelog->getName());
        $bind = [$this->getColumnName() => $useCache];
        $where = [
            'version_id > ?' => (int) $oldVersionId,
            'version_id <= ?' => (int) $currentVersionId,
            'entity_id IN(?)' => $entityIds
        ];

        $this->connection->update($changelogTableName, $bind, $where);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @param bool $useCache
     * @param bool $fromVersionId
     * @param bool $toVersionId
     * @return int[]
     */
    public function retrieveUseCacheEntityIds($changelog, $useCache = true, $fromVersionId, $toVersionId)
    {
        if ($useCache !== null) {
            $useCache = $this->normalizeUseCache($useCache);
        }

        $changelogTableName = $this->resource->getTableName($changelog->getName());
        if (!$this->connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $select = $this->connection->select()->distinct(
            true
        )->from(
            $changelogTableName,
            [$changelog->getColumnName()]
        )->where(
            'version_id > ?',
            (int) $fromVersionId
        )->where(
            'version_id <= ?',
            (int) $toVersionId
        );

        if ($useCache !== null) {
            $select->where(
                $this->getColumnName() . ' = ?',
                $useCache
            );
        }

        $entityIds =  $this->connection->fetchCol($select);
        if (is_array($entityIds)) {
            $entityIds = array_combine(array_keys($entityIds), array_values($entityIds));
        }
        return $entityIds;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getColumnName()
    {
        return self::COLUMN_NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @param bool $useCache
     * @return int
     */
    public function normalizeUseCache($useCache)
    {
        return $useCache ? 1 : 0;
    }
}
