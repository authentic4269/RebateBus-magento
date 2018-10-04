<?php
namespace Bus\Rebate\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('quote_item_rebate')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('quote_item_rebate')
            )
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Rebate ID'
            )
            ->addColumn(
                'verification',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Verification Code'
            )
            ->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'ID of quote_item to which Rebate is Applied'
            )
            ->addColumn(
                'amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false],
                'Rebate Amount'
            )
            ->addColumn(
                'max_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false],
                'Max # of Products to which Rebate can Apply'
            )
            ->addColumn(
                'cap',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                1,
                ['nullable' => false],
                'Rebate Cap as % of Cost'
            )
            ->addColumn(
                'bus_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'Rebate Bus Cookie'
            )
            ->addColumn(
                'program',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Rebate Program'
            )
             ->addColumn(
                'invoice_item_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Invoice Item Name'
            )
             ->addColumn(
                'min_contribution',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                255,
                [],
                'Minimum Customer Contribution'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Rebate Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Rebate Updated At'
            )
            ->setComment('Rebates Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('quote_item_rebate'),
                $setup->getIdxName(
                    $installer->getTable('quote_item_rebate'),
                    ['item_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['item_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );

	    $quoteTable = $installer->getTable('quote_item');
	    $connection->addColumn($quoteTable, "rebate", ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT, 'nullable' => true, 'comment' => 'rebate']);

        }
        $installer->endSetup();
    }
}
