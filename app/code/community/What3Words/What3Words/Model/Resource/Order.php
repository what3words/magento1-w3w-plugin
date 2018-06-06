<?php

/**
 * Class What3Words_What3Words_Model_Resource_Order
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Resource_Order
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('what3words/order', 'entity_id');
    }

    /**
     * Create an entry in the w3w order table
     * @param $param
     */
    public function saveToTable($data)
    {
        $table = $this->getMainTable();

        $this->_getWriteAdapter()->insert(
            $table, array(
                'order_id' => $data['order_id'],
                'w3w' => $data['w3w']
            )
        );
    }
}
