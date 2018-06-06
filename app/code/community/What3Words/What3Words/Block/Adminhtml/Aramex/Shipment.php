<?php

/**
 * Class What3Words_What3Words_Block_Adminhtml_Aramex_Shipment
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Adminhtml_Aramex_Shipment
    extends What3Words_What3Words_Block_Abstract
{
    /**
     * Get frontend ID from XML
     * @return mixed
     */
    public function getInputLabel()
    {
        return $this->getFrontendId();
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getW3wByOrderId($orderId)
    {
        $what3words = Mage::getModel('what3words/order')
            ->getCollection()
            ->loadByOrderId($orderId);

        return $what3words['w3w'];
    }
}
