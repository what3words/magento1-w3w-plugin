<?php

/**
 * Class What3Words_What3Words_Block_Abstract
 * @author Vicki Tingle
 */
abstract class What3Words_What3Words_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * Default input label
     * @return string
     */
    public function getInputLabel()
    {
        return 'what3words';
    }

    /**
     * Get helper to retrieve config data
     * @return Mage_Core_Helper_Abstract
     */
    public function getConfigHelper()
    {
        return Mage::helper('what3words/config');
    }

    /**
     * Get the current customer
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get the allowed countries from the config
     * @return array
     */
    public function getCountriesArray()
    {
        $countryString = Mage::helper('what3words/config')->getAllowedCountries();
        return explode(',', $countryString);
    }

    /**
     * Get available W3W address IDs to be appended to select options
     * @return string
     */
    public function getAvailableAddressIds()
    {
        $addressModel = Mage::getModel('what3words/address')
            ->getCollection();
        $addressIds = array();

        if ($items = $addressModel->getW3wAddressesByCustomer(
            $this->getCustomer()->getCustomerId())) {
            foreach ($items as $item) {
                $addressIds[] = array(
                    'address_id' => $item['address_id'],
                    'w3w' => $item['w3w']
                );
            }
        }
        return $addressIds;
    }

    /**
     * Find out if the current locale is Arabic
     * @return bool
     */
    public function getIsArabicLocale()
    {
        $locale = Mage::getStoreConfig(
            'general/locale/code',Mage::app()->getStore()->getId()
        );

        if ($isArabic = strpos($locale, 'ar-')) {
            return true;
        }
        return false;
    }
}
