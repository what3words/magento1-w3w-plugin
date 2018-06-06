<?php
/**
 * Class What3Words_What3Words_Block_Setup
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Setup extends What3Words_What3Words_Block_Abstract
{
    /** Only render the setup block if the module is enabled and the API key has been entered.
     * @return bool|string
     */
    protected function _toHtml()
    {
        /**
         * @var $config What3Words_What3Words_Helper_Config
         */
        $config = $this->getConfigHelper();
        $apiKey = $config->getApiKey();
        if ($config->getIsEnabled() && $apiKey) {
            return parent::_toHtml();
        }
        return false;
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('what3words/ajax/validate');
    }

    /**
     * @return string
     */
    public function getValidateCountryUrl()
    {
        return $this->getUrl('what3words/ajax/validateCountry');
    }

    public function getAddressUrl()
    {
        return $this->getUrl('what3words/ajax/getAddress');
    }
}
