<?php

/**
 * Class What3Words_What3Words_Model_Observer
 *
 * @author Vicki Tingle
 *
 */
class What3Words_What3Words_Model_Observer
{
    /**
     * Save the W3W value to W3W quote table
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function saveToQuoteTable(Varien_Event_Observer $observer)
    {
        // Get the params from the request
        $requestParams = Mage::app()->getRequest()->getParams();
        /** @var What3Words_What3Words_Helper_Data $helper */
        $helper = Mage::helper('what3words');
        $data = array();
        $data['address_id'] = null;
        $quoteTable = Mage::getResourceModel('what3words/quote');
        if ($currentQuote = $this->_getQuote()) {

            // If the customer is using a previously saved address
            if (isset($requestParams['billing_address_id'])
                && $requestParams['billing_address_id'] !== ''
            ) {
                $w3wAddressItem = Mage::getModel('what3words/address')
                    ->getCollection()
                    ->loadByAddressId($requestParams['billing_address_id']);

                // Set the current lot of data
                if ($w3wAddressItem) {
                    $data['quote_id'] = $currentQuote->getId();
                    $data['w3w'] = $w3wAddressItem['w3w'];
                    $data['address_id'] = $requestParams['billing_address_id'];
                    $quoteTable->saveToTable($data);
                    return $this;
                }
            }
            // If we're saving the billing address
            if (isset($requestParams['what3words-billing'])
                && $requestParams['what3words-billing'] !== ''
            ) {
                // Here we save a billing or shipping address ID
                if (isset($requestParams['billing_address_id'])
                    && isset($requestParams['billing']['save_in_address_book'])
                    && $requestParams['billing_address_id'] != ''
                ) {
                    $data['address_id'] = $requestParams['billing_address_id'];
                }

                $data['quote_id'] = $currentQuote->getId();
                $data['w3w'] = $requestParams['what3words-billing'];

                $quoteTable = Mage::getResourceModel('what3words/quote');
                // Insert an entry into the sales_quote_w3w table
                $quoteTable->saveToTable($data);
            }

            // If the customer is selecting a shipping address
            // the three word address chosen here will always
            // take priority
            if (isset($requestParams['what3words-shipping'])
                && $requestParams['what3words-shipping'] !== null
            ) {
                if ($requestParams['shipping_address_id'] != ''
                    && isset($requestParams['shipping_address_id'])
                ) {
                    $data['quote_id'] = $currentQuote->getId();
                    $data['w3w'] = $requestParams['what3words-shipping'];
                }

                $data['quote_id'] = $currentQuote->getId();
                $data['w3w'] = $requestParams['what3words-shipping'];

                if (isset($requestParams['shipping']['address_id'])
                    && isset($requestParams['shipping']['save_in_address_book'])
                ) {
                    $data['address_id'] = $requestParams['shipping']['address_id'];
                }

                // Check for existing entry from quote
                $existingItem = $helper->getW3wByQuoteId($data['quote_id']);

                // If we've got an existing entry from billing, update it
                if ($existingItem) {
                    $quoteTable->updateFromShipping($data);
                } else {
                    // Otherwise add a new one
                    $quoteTable->saveToTable($data);
                }
            } elseif (isset($requestParams['billing_address_id'])
                && (!isset($requestParams['what3words-billing'])
                || !isset($requestParams['what3words-shipping']))
            ) {
                // The customer may be using a saved address
                //with a W3W value saved against it
                $what3Words = Mage::getModel('what3words/address')
                    ->getW3wByAddressId($requestParams['billing_address_id']);

                if ($what3Words) {
                    $data['quote_id'] = $currentQuote->getId();
                    $data['w3w'] = $what3Words;

                    $quoteTable = Mage::getResourceModel('what3words/quote');
                    $quoteTable->saveToTable($data);
                }
            }
        }
        return $this;
    }

    /**
     * Save our W3W field against into sales_order_w3w
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveToOrderTable(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('what3words');
        /**
         * @var $currentQuote Mage_Sales_Model_Quote
         */
        // Get the associated W3W quote item
        $currentQuote = $this->_getQuote();
        $w3wQuoteItem = $helper->getW3wByQuoteId($currentQuote->getId());
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($w3wQuoteItem) {
            // Get the order
            $data = array(
                'order_id' => $order->getId(),
                'w3w' => $w3wQuoteItem['w3w']
            );

            // Save the item
            $orderTable = Mage::getResourceModel('what3words/order');
            $orderTable->saveToTable($data);
        }
        return $this;
    }

    /**
     * Create an entry if a customer has saved their address
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveToAddressTableFromCheckout(Varien_Event_Observer $observer)
    {
        $currentQuote = $this->_getQuote();

        /** @var What3Words_What3Words_Helper_Data $helper */
        $helper = Mage::helper('what3words');
        /** @var What3Words_What3Words_Model_Address $address */
        $address = Mage::getModel('what3words/address');

        // Get the quote Item and if an address ID is saved, create an address entry
        $quoteItem = $helper->getW3wByQuoteId($currentQuote->getId());
        $customerAddressId = $address->getCustomerAddressId(
            $observer->getEvent()->getOrder()->getId()
        );
        // Get the customer address ID from the sales_flat_order_address entry
        if ($customerAddressId && $quoteItem) {
            $customerAddress = $this->getCustomerAddress($customerAddressId);
            $customerAddress->setData('w3w', $quoteItem['w3w']);
            $customerAddress->save();

            $data = array(
                'address_id' => $customerAddressId,
                'w3w' => $quoteItem['w3w']
            );
            Mage::getResourceModel('what3words/address')
                ->saveToTable($data);
        }

        return $this;
    }

    /**
     * Update the W3W address table when a customer updates their address
     * from their account
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveToAddressTableFromAccount(Varien_Event_Observer $observer)
    {
        $requestParams = Mage::app()->getRequest()->getParams();
        $customerAddress = $observer->getCustomerAddress();
        $addressId = $customerAddress->getEntityId();
        /** @var What3Words_What3Words_Helper_Data $helper */
        $helper = Mage::helper('what3words');
        if (isset($requestParams['what3words-customer'])
            && $requestParams['what3words-customer'] != ''
        ) {
            // Get the address ID from the request
            if ($addressId) {
                $addressItem = $helper->getW3wByAddressId($addressId);

                $data = array(
                    'address_id' => $addressId,
                    'w3w' => $requestParams['what3words-customer']
                );

                // We either want to update an existing entry or add a new one
                if (isset($addressItem['address_id'])) {
                    $addressTable = Mage::getResourceModel('what3words/address');
                    $addressTable->updateFromCustomerAccount($data);
                } else {

                    Mage::getResourceModel('what3words/address')
                        ->saveToTable($data);
                }
            }
        }
        return $this;
    }

    /**
     * Ensure the w3w address attribute also gets saved
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveAttribute(Varien_Event_Observer $observer)
    {
        $requestParams = Mage::app()->getRequest()->getParams();
        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = $observer->getCustomerAddress();
        if (isset($requestParams['what3words-customer'])) {
            $customerAddress->setData('w3w', $requestParams['what3words-customer']);
        }
        return $this;
    }

    /**
     * Render our 3 word address block in the sales order view
     * in the admin
     * @param Varien_Event_Observer $observer
     */
    public function getSalesOrderViewInfo(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (($block->getNameInLayout() == 'order_info')
            && ($child = $block->getChild(
                'what3words.address.info'
            ))
        ) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }

    /**
     * Save 3 word address from admin customer creation/editing
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveAdminCustomerToTable(Varien_Event_Observer $observer)
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $observer->getCustomer();
        $data = $observer->getRequest()->getParams();
        /** @var What3Words_What3Words_Helper_Data $helper */
        $helper = Mage::helper('what3words');

        // Get the address IDs from the request
        foreach (array_keys($data['address']) as $index) {
            if (isset($data['address'][$index]['w3w'])
                && $data['address'][$index]['w3w'] !== ''
            ) {
                Mage::log($index, null, 'vickitest.log');

                /** @var What3Words_What3Words_Model_Validation $validationModel */
                $validationModel = Mage::getModel('what3words/validation');
                $result = $validationModel->validate(
                    $data['address'][$index]['w3w'],
                    $data['address'][$index]['country_id']
                );

                // Make sure we're only using actual address IDs
                // and get the one being edited
                if (!is_numeric($index)) {
                    Mage::log($index, null, 'vickitest.log');

                    $addresses = $customer->getAddressesCollection()
                        ->setOrder('created_at', 'desc');
                    $address = $addresses->getFirstItem();
                    $addressId = $address->getData('entity_id');
                    $addressId++;
                    Mage::log($addressId, null, 'vickitest.log');

                } else {
                    $address = $this->getCustomerAddress($index);
                    $addressId = $index;
                }

                // If the three words were validated, save them
                // both to table and to address attribute
                if ($result['success']) {
                    $addressItem = $helper->getW3wByAddressId($addressId);
                    $w3wData = array(
                        'address_id' => $addressId,
                        'w3w' => $data['address'][$index]['w3w']
                    );
                    Mage::log($w3wData, null, 'vickitest.log');
                    if (isset($addressItem['address_id'])) {
                        /** @var $addressTable What3Words_What3Words_Model_Resource_Address */
                        $addressTable = Mage::getResourceModel('what3words/address');
                        $addressTable->updateFromCustomerAccount($w3wData);
                    } else {
                        Mage::getResourceModel('what3words/address')
                            ->saveToTable($w3wData);
                    }
                } else {
                    $address->setData('w3w', '');
                    $address->save();
                    /** @var Mage_Core_Model_Session $session */
                    $session = Mage::getModel('core/session');
                    // We aren't validating over AJAX so just add a message to the session
                    $session->addError($result['message']);
                }
            }
        }
        return $this;
    }

    /**
     * Return a customer address by address ID
     * @param $addressId
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomerAddress($addressId)
    {
        return Mage::getModel('customer/address')->load($addressId);
    }

    /**
     * Get the current quote
     * @return mixed
     */
    private function _getQuote()
    {
        return Mage::getModel('checkout/cart')->getQuote();
    }

    /**
     * Add the include path to the What3Words library folder
     *
     * @return $this
     */
    public function addIncludePath()
    {
        self::initIncludePath();

        return $this;
    }

    /**
     * Add the include path needed for the W3W PHP wrapper
     */
    public static function initIncludePath()
    {
        require_once(Mage::getBaseDir('lib') . DS . 'What3Words' . DS . 'autoload.php');
    }
}

