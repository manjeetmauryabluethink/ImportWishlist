<?php
namespace Bluethinkinc\ImportWishlist\Model\ResourceModel\Grid;

/* use required classes */
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = "wishlist_item_id";

    /**
     * Construct
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init(
            \Magento\Wishlist\Model\Item::class,
            \Magento\Wishlist\Model\ResourceModel\Item::class
        );

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->storeManager = $storeManager;
    }

    /**
     * Using initSelect perform join
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ["secondTable" => $this->getTable("wishlist")],
                "`main_table`.wishlist_id= `secondTable`.wishlist_id",
                ["secondTable.customer_id"]
            )
            ->joinLeft(
                ["thirdTable" => $this->getTable("customer_entity")],
                "secondTable.customer_id = thirdTable.entity_id",
                ["email"]
            )
            ->joinLeft(
                ["forth_table" => $this->getTable("catalog_product_entity")],
                "main_table.product_id = forth_table.entity_id",
                ["sku"]
            );
    }
}
