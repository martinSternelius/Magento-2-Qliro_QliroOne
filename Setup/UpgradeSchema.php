<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Qliro\QliroOne\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use Qliro\QliroOne\Model\LogRecord as LogRecordModel;
use Qliro\QliroOne\Model\ResourceModel\LogRecord;
use Qliro\QliroOne\Model\Link as LinkModel;
use Qliro\QliroOne\Model\ResourceModel\Link;
use Qliro\QliroOne\Model\OrderManagementStatus as OrderManagementStatusModel;
use Qliro\QliroOne\Model\ResourceModel\OrderManagementStatus;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const TABLE_QUOTE_ADDRESS = 'quote_address';
    const TABLE_SALES_ORDER = 'sales_order';
    const TABLE_SALES_INVOICE = 'sales_invoice';
    const TABLE_SALES_CREDITMEMO = 'sales_creditmemo';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '0.1.2') < 0) {
            $connection->addColumn($setup->getTable(LogRecord::TABLE_LOG), LogRecordModel::FIELD_REFERENCE, [
                'type' => Table::TYPE_TEXT,
                25,
                'comment' => 'Merchant ID',
            ]);
            $connection->addColumn($setup->getTable(LogRecord::TABLE_LOG), LogRecordModel::FIELD_TAGS, [
                'type' => Table::TYPE_TEXT,
                256,
                'comment' => 'Comma separated list of tags',
            ]);
            $connection->dropColumn($setup->getTable(LogRecord::TABLE_LOG), 'tag');
        }

        if (version_compare($context->getVersion(), '0.1.3') < 0) {
            //Quote address tables
            $connection->addColumn($setup->getTable(self::TABLE_QUOTE_ADDRESS), 'qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_QUOTE_ADDRESS), 'base_qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount',
                ]);

            //Order tables
            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'base_qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'qliroone_fee_refunded', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount Refunded',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'base_qliroone_fee_refunded', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount Refunded',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'qliroone_fee_invoiced', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Amount Invoiced',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'base_qliroone_fee_invoiced', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount Invoiced',
                ]);

            //Invoice tables
            $connection->addColumn($setup->getTable(self::TABLE_SALES_INVOICE), 'qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_INVOICE), 'base_qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount',
                ]);

            //Credit memo tables
            $connection->addColumn($setup->getTable(self::TABLE_SALES_CREDITMEMO), 'qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_CREDITMEMO), 'base_qliroone_fee', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Amount',
                ]);
            //Quote address tables
            $connection->addColumn($setup->getTable(self::TABLE_QUOTE_ADDRESS), 'qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Tax Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_QUOTE_ADDRESS), 'base_qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Tax Amount',
                ]);

            //Order tables
            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Tax Amount',

                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_ORDER), 'base_qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Tax Amount',
                ]);

            //Invoice tables
            $connection->addColumn($setup->getTable(self::TABLE_SALES_INVOICE), 'qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Tax Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_INVOICE), 'base_qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Tax Amount',
                ]);

            //Credit memo tables
            $connection->addColumn($setup->getTable(self::TABLE_SALES_CREDITMEMO), 'qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Fee Tax Amount',
                ]);

            $connection->addColumn($setup->getTable(self::TABLE_SALES_CREDITMEMO), 'base_qliroone_fee_tax', [
                    'type' => Table::TYPE_DECIMAL,
                    '12,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Base Fee Tax Amount',
                ]);
        }

        if (version_compare($context->getVersion(), '0.1.4') < 0) {
            $connection->addColumn($setup->getTable(Link::TABLE_LINK), LinkModel::FIELD_QLIRO_ORDER_STATUS, [
                'type' => Table::TYPE_TEXT,
                32,
                'comment' => 'Qliro Order Status',
            ]);
        }

        if (version_compare($context->getVersion(), '0.1.5') < 0) {
            $connection->addColumn($setup->getTable(Link::TABLE_LINK), LinkModel::FIELD_REMOTE_IP, [
                'type' => Table::TYPE_TEXT,
                32,
                'comment' => 'Client IP when link was created',
            ]);
        }

        if (version_compare($context->getVersion(), '0.1.6') < 0) {
            if (!$setup->tableExists(OrderManagementStatus::TABLE_OM_STATUS)) {
                $table = $setup->getConnection()
                    ->newTable($setup->getTable(OrderManagementStatus::TABLE_OM_STATUS))
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_ID,
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Id'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_DATE,
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Date'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_TRANSACTION_ID,
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Payment Transaction ID'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_RECORD_TYPE,
                        Table::TYPE_TEXT,
                        25,
                        [],
                        'Record Type'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_RECORD_ID,
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Record ID'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_TRANSACTION_STATUS,
                        Table::TYPE_TEXT,
                        255,
                        [],
                        'Transaction Status'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_NOTIFICATION_STATUS,
                        Table::TYPE_TEXT,
                        10,
                        [],
                        'Notification Status'
                    )
                    ->addColumn(
                        OrderManagementStatusModel::FIELD_MESSAGE,
                        Table::TYPE_TEXT,
                        null,
                        [],
                        'Possible Message'
                    )
                    ->addIndex(
                        $setup->getIdxName(OrderManagementStatus::TABLE_OM_STATUS, [OrderManagementStatusModel::FIELD_DATE]),
                        [OrderManagementStatusModel::FIELD_DATE]
                    )
                    ->addIndex(
                        $setup->getIdxName(OrderManagementStatus::TABLE_OM_STATUS, [OrderManagementStatusModel::FIELD_TRANSACTION_ID]),
                        [OrderManagementStatusModel::FIELD_TRANSACTION_ID]
                    )
                    ->addIndex(
                        $setup->getIdxName(OrderManagementStatus::TABLE_OM_STATUS, [OrderManagementStatusModel::FIELD_TRANSACTION_STATUS]),
                        [OrderManagementStatusModel::FIELD_TRANSACTION_STATUS]
                    )
                    ->addIndex(
                        $setup->getIdxName(OrderManagementStatus::TABLE_OM_STATUS, [OrderManagementStatusModel::FIELD_RECORD_ID]),
                        [OrderManagementStatusModel::FIELD_RECORD_ID]
                    )
                    ->setComment('QliroOne OM Notification Statuses');
                $setup->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '0.1.7') < 0) {
            $connection->addColumn($setup->getTable(OrderManagementStatus::TABLE_OM_STATUS), OrderManagementStatusModel::FIELD_QLIRO_ORDER_ID, [
                'type' => Table::TYPE_INTEGER,
                'length' => 10,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Qliro Order Id',
            ]);
        }

        if (version_compare($context->getVersion(), '0.1.8') < 0) {
            $connection->addColumn($setup->getTable(Link::TABLE_LINK), LinkModel::FIELD_PLACED_AT, [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'default' => null,
                'comment' => 'When pending is opened',
            ]);
        }

        if (version_compare($context->getVersion(), '1.2.3') < 0) {
            $connection->addColumn($setup->getTable(Link::TABLE_LINK), LinkModel::FIELD_UNIFAUN_SHIPPING_AMOUNT, [
                'type' => Table::TYPE_FLOAT,
                'nullable' => true,
                'default' => null,
                'comment' => 'If unifaun is used it stores the freight amount here for the shipping method to read',
            ]);
        }

        $setup->endSetup();
    }
}
