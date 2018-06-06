<?php

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute(
    'customer_address', 'w3w',
    array(
        'type' => 'varchar',
        'input' => 'text',
        'label' => '3 word address',
        'global' => 1,
        'visible' => 1,
        'required' => 0,
        'user_defined' => 1,
        'visible_on_front' => 1
    )
);

Mage::getSingleton('eav/config')
    ->getAttribute('customer_address', 'w3w')
    ->setData('used_in_forms', array('adminhtml_customer_address'))
    ->save();

$installer->endSetup();
