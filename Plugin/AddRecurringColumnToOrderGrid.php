<?php
namespace Qliro\QliroOne\Plugin;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Zend_Db_Expr;

class AddRecurringColumnToOrderGrid
{
    public function beforeLoad(OrderGridCollection $collection)
    {
        $select = $collection->getSelect();
        
        // Add the join to include recurring order information
        $select->joinLeft(
            ['recurring_info' => $collection->getTable('qliroone_recurring_info')],
            'main_table.entity_id = recurring_info.original_order_id',
            []
        );

        // Add the is_recurring expression as a part of the field list
        $select->columns([
            'is_recurring' => new Zend_Db_Expr('IF(recurring_info.original_order_id IS NOT NULL AND recurring_info.next_order_date IS NOT NULL, "Yes", "No")')
        ]);

        return $collection;
    }
}
