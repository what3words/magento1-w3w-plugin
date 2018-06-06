<?php

/**
 * Class What3Words_What3Words_Helper_Data
 * @author Vicki Tingle
 */
class What3Words_What3Words_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get an item from the W3W quote table
     * @param $quoteId
     * @return mixed
     */
    public function getW3wByQuoteId($quoteId)
    {
        $quoteItem = Mage::getModel('what3words/quote')
            ->getCollection()
            ->loadByQuoteId($quoteId);

        return $quoteItem;
    }

    /**
     * Get an order from the W3W order table
     * @param $orderId
     * @return bool | What3Words_What3Words_Model_Order
     */
    public function getW3wByOrderId($orderId)
    {
        if ($orderItem = Mage::getModel('what3words/order')->getCollection()->loadByOrderId($orderId)) {
            return $orderItem;
        }
        return false;
    }

    /**
     * Get an address ID from the W3W address table
     * @param $addressId
     * @return mixed
     */
    public function getW3wByAddressId($addressId)
    {
        $addressItem = Mage::getModel('what3words/address')
            ->getCollection()
            ->loadByAddressId($addressId);

        return $addressItem;
    }
}
