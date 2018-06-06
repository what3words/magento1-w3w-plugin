<?php

/**
 * Class What3Words_What3Words_Model_Resource_Quote
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Resource_Quote
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('what3words/quote', 'entity_id');
    }

    /**
     * Create an entry in the w3w quote table
     * @param $param
     */
    public function saveToTable($data)
    {
        $table = $this->getMainTable();

        $this->_getWriteAdapter()->insert(
            $table, array(
            'quote_id' => $data['quote_id'],
            'w3w' => $data['w3w'],
            'address_id' => $data['address_id']
            )
        );
    }

    /**
     * Update the quote table if a different shipping address has been used
     * @param $data
     */
    public function updateFromShipping($data)
    {
        $table = $this->getMainTable();
        $this->_getWriteAdapter()->update(
            $table, array(
                'quote_id' => $data['quote_id'],
                'w3w' => $data['w3w'],
                'address_id' => $data['address_id']
            ), 'quote_id = ' . $data['quote_id']
        );
    }
}
