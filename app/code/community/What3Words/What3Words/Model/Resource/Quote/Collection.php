<?php

/**
 * Class What3Words_What3Words_Model_Resource_Quote_Collection
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Resource_Quote_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('what3words/quote');
    }

    /**
     * Load a database entry by quote ID
     * @param $quoteId
     * @return bool|Varien_Object
     */
    public function loadByQuoteId($quoteId)
    {
        $item = $this->addFieldToFilter('quote_id', $quoteId)
            ->setOrder('entity_id', 'DESC')
            ->getFirstItem();
        if (!is_null($item) && isset($item['w3w'])) {
            return $item;
        }
        return false;
    }
}
