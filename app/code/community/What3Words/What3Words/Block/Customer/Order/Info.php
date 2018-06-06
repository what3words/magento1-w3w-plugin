<?php

/**
 * Class What3Words_What3Words_Block_Customer_Order_Info
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Customer_Order_Info
    extends What3Words_What3Words_Block_Abstract
{
    /**
     * Get 3 word address associated with this order
     * @return bool
     */
    public function getW3wByOrderId()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $w3wOrderItem = Mage::getModel('what3words/order')
            ->getCollection()->loadByOrderId($orderId);

        if ($w3wOrderItem) {
            return $w3wOrderItem['w3w'];
        }
        return false;
    }
}
