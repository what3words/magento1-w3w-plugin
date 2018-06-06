<?php

/**
 * Class What3Words_What3Words_Block_Customer_Address
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Customer_Address
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
     * Get the W3W for a specific address
     * @return bool
     */
    public function getW3wByAddressId()
    {
        $addressItem = Mage::helper('what3words')
            ->getW3wByAddressId($this->getAddressId());
        if ($addressItem && isset($addressItem['w3w'])) {
            return $addressItem['w3w'];
        } else {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel('customer/session')->getCustomer();
            if ($address = $customer->getAddressById($this->getAddressId())) {
                if ($address->getData('w3w')) {
                    return $address->getData('w3w');
                }
            }
        }
        return false;
    }

    /**
     * Get the ID for current address being edited
     * @return mixed
     */
    public function getAddressId()
    {
        return $this->getRequest()->getParam('id');
    }
}

