<?php
/**
 * Copyright © 2016 Innovo. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Innovo\CacheImprove\Model;

/**
 * Class CategoryProvider
 */
class CategoryProvider
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
     * Retrieve category ids assigned to product ids
     *
     * @param array $productIds
     * @return array
     */
    public function retrieveCategoryIdsByProductIds($productIds)
    {
        if (empty($productIds)) {
            return [];
        }
        $select = $this->connection->select()
            ->distinct(true)
            ->from($this->resource->getTableName('catalog_category_product'), ['category_id'])
            ->where('product_id IN(?)', $productIds);

        $categoryIds = $this->connection->fetchCol($select);
        if (empty($categoryIds)) {
            $categoryIds = [];
        }
        return $categoryIds;
    }
}
