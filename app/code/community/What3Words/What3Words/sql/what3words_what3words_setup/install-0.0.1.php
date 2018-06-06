<?php
/**
 * @author Vicki Tingle <vicki@gene.co.uk
 * @var $installer Mage_Eav_Model_Entity_Setup
 */
$installer = $this;

$installer->startSetup();

$quoteTable = $installer->getConnection()
    ->newTable($installer->getTable('what3words/quote'))
    ->addColumn(
        'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'auto_increment' => true
        ),
        'ID'
    )
    ->addColumn(
        'quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ),
        'Quote ID'
    )
    ->addColumn(
        'w3w', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        'length' => 64
        ),
        'What3Words'
    )
    ->addForeignKey(
        $this->getFkName(
            'what3words/quote', 'quote_id', 'sales/quote', 'entity_id'
        ),
        'quote_id', $this->getTable('sales/quote'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($quoteTable);

$orderTable = $installer->getConnection()
    ->newTable($installer->getTable('what3words/order'))
    ->addColumn(
        'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'auto_increment' => true
        ),
        'ID'
    )
    ->addColumn(
        'order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ),
        'Order ID'
    )
    ->addColumn(
        'w3w', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        'length' => 64
        ),
        'What3Words'
    )
    ->addForeignKey(
        $this->getFkName(
            'what3words/quote', 'order_id', 'sales/order', 'entity_id'
        ),
        'order_id', $this->getTable('sales/order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$installer->getConnection()->createTable($orderTable);

$customerTable = $installer->getConnection()
    ->newTable($installer->getTable('what3words/address'))
    ->addColumn(
        'entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'auto_increment' => true
        ),
        'ID'
    )
    ->addColumn(
        'address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        ),
        'Address ID'
    )
    ->addColumn(
        'w3w', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        'length' => 64
        ),
        'What3Words'
    );

$installer->getConnection()->createTable($customerTable);