<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

/**
 * Class ProductProvider
 */
class ProductProvider
{
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
    }

    /**
     * Retrieve parent products ids assigned
     *
     * @param array $productIds
     * @return array
     */
    public function retrieveParentProductIds($productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $select = $this->connection->select()
            ->distinct(true)
            ->from($this->resource->getTableName('catalog_product_relation'), ['parent_id', 'child_id'])
            ->where('child_id IN(?)', $productIds);

        $parentIds = $this->connection->fetchAll($select);
        if (empty($parentIds)) {
            return [];
        }
        $return = [];
        foreach ($parentIds as $row) {
            $return[$row['child_id']] = $row['parent_id'];
        }
        return $return;
    }
}
