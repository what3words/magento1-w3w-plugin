<?php

/**
 * Class What3Words_What3Words_Model_Resource_Address
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Resource_Address
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('what3words/address', 'entity_id');
    }

    /**
     * Create an entry in the w3w address table
     * @param $data
     */
    public function saveToTable($data)
    {
        $table = $this->getMainTable();

        $this->_getWriteAdapter()->insert(
            $table, array(
                'address_id' => $data['address_id'],
                'w3w' => $data['w3w']
            )
        );
    }

    /**
     * Update the address table if the customer has updated their address
     * @param $data
     */
    public function updateFromCustomerAccount($data)
    {
        $table = $this->getMainTable();

        $this->_getWriteAdapter()->update(
            $table, array(
            'address_id' => $data['address_id'],
            'w3w' => $data['w3w'],
            ), 'address_id = ' . $data['address_id']
        );
    }
}
