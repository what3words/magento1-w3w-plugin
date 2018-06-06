<?php

$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $installer->getTable('what3words/quote'),
        'address_id',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'nullable' => true,
            'comment' => 'address_id',
        )
    );

$installer->endSetup();
