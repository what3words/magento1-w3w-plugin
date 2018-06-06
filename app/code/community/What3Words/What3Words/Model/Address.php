<?php

/**
 * Class What3Words_What3Words_Model_Address
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Address extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('what3words/address');
    }

    /**
     * Get the customer address ID from the sales_flat_order_address table
     * @param $orderId
     * @return bool
     */
    public function getCustomerAddressId($orderId)
    {
        /**
         * @var $address Mage_Customer_Model_Resource_Address_Collection
         */
        $address = Mage::getResourceModel('sales/order_address_collection')
            ->addFieldToFilter('parent_id', $orderId)
            ->addFieldToFilter('customer_address_id', array('neq' => null))
            ->setOrder('entity_id', 'desc')
            ->getFirstItem();

        if (!is_null($address)) {
            return $address['customer_address_id'];
        }
        return false;
    }

    /**
     * Get an individual W3W item by customer address ID
     * @param $addressId
     * @return bool
     */
    public function getW3wByAddressId($addressId)
    {
        $addressItem = $this->getCollection()
            ->addFieldToFilter('address_id', $addressId)->getFirstItem();

        if (!is_null($addressItem) && isset($addressItem['w3w'])) {
            return $addressItem['w3w'];
        }
        return false;
    }
}
