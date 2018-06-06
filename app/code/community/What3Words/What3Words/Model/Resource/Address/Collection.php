<?php

/**
 * Class What3Words_What3Words_Model_Resource_Address_Collection
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Resource_Address_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('what3words/address');
    }

    /**
     * Get a DB entry by its address ID
     * @param $addressId
     * @return bool|Varien_Object
     */
    public function loadByAddressId($addressId)
    {
        $item = $this->addFieldToFilter('address_id', $addressId)->getFirstItem();
        if (!is_null($item)) {
            return $item;
        }
        return false;
    }

    /**
     * Get any W3W items associated to a particular customer
     * @param $customerId
     * @return bool|Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getW3wAddressesByCustomer($customerId)
    {
        /**
         * @var $address Mage_Sales_Model_Entity_Order_Address_Collection
         */
        $addressIds = Mage::getResourceModel('customer/address_collection')
            ->addFieldToFilter('parent_id', $customerId);
        $w3wIds = array();

        foreach ($addressIds as $addressId) {
            $w3wIds[] = $addressId['entity_id'];
        }
        $w3wItems = $this->addFieldToFilter(
            'address_id', array('in' => $w3wIds)
        )->load();

        if ($w3wItems->getSize() > 0) {
            return $w3wItems;
        }

        return false;
    }
}
