<?php

/**
 * Class What3Words_What3Words_Block_Adminhtml_Address_Info
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Adminhtml_Address_Info
    extends Mage_Adminhtml_Block_Sales_Order_View
{
    /**
     * Get the 3 word address associated with this order
     * @return bool|What3Words_What3Words_Model_Order
     */
    public function getW3wFromOrder()
    {
        /** @var What3Words_What3Words_Helper_Data $helper */
        $helper = Mage::helper('what3words');
        $orderId = $this->getOrderId();

        return $helper->getW3wByOrderId($orderId);
    }
}
