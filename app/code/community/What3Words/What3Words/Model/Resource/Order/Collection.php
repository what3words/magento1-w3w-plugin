<?php

/**
 * Class What3Words_What3Words_Model_Resource_Order_Collection
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Resource_Order_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('what3words/order');
    }

    /**
     * Load a database entry by order id
     * @param $orderId
     * @return bool|Varien_Object
     */
    public function loadByOrderId($orderId)
    {
        $item = $this->addFieldToFilter('order_id', $orderId)->getFirstItem();
        if (!is_null($item) && !empty($item->getData())) {
            return $item;
        }
        return false;
    }
}
