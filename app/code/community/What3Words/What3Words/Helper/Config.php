<?php

/**
 * Class What3Words_What3Words_Helper_Config
 * @author Vicki Tingle
 */
class What3Words_What3Words_Helper_Config extends What3Words_What3Words_Helper_Data
{
    /**
     * Is the module enabled?
     * @return mixed
     */
    public function getIsEnabled()
    {
        return Mage::getStoreConfig('what3words/general/enabled');
    }

    /**
     * Get API key from config
     * @return mixed
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig('what3words/general/api_key');
    }

    /**
     * Get the list of allowed countries
     * @return mixed
     */
    public function getAllowedCountries()
    {
        return Mage::getStoreConfig('what3words/general/allowed_countries');
    }

    /**
     * @return mixed
     */
    public function getInputLabelColor()
    {
        return Mage::getStoreConfig('what3words/frontend/label_color');
    }

    /**
     * @return mixed
     */
    public function getInputLabelSize()
    {
        return Mage::getStoreConfig('what3words/frontend/label_size');
    }

    /**
     * @return mixed
     */
    public function getPlaceHolder()
    {
        return Mage::getStoreConfig('what3words/frontend/placeholder');
    }

    /**
     * @return mixed
     */
    public function getTypeaheadDelay()
    {
        return Mage::getStoreConfig('what3words/frontend/typeahead_delay');
    }
}
