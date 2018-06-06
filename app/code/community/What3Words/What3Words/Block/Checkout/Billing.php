<?php

/**
 * Class What3Words_What3Words_Block_Checkout_Billing
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Checkout_Billing
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
     * @param $addressId
     * @return mixed
     */
    public function getW3wFromAddressId($addressId)
    {
        $w3wAddressItem = Mage::helper('what3words')->getW3wByAddressId($addressId);
        return $w3wAddressItem['w3w'];
    }
}
