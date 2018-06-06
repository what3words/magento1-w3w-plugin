<?php

/**
 * Class What3Words_What3Words_Block_Checkout_Shipping
 * @author Vicki Tingle
 */
class What3Words_What3Words_Block_Checkout_Shipping
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
}
