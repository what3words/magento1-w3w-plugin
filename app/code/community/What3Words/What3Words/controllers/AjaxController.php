<?php

/**
 * Class What3Words_What3Words_AjaxController
 * @author Vicki Tingle
 */
class What3Words_What3Words_AjaxController extends Mage_Core_Controller_Front_Action
{
    /**
     * Frontend validation
     * Utilise the W3W PHP Wrapper to check if an W3W value is valid
     */
    public function validateAction()
    {
        $what3Words = $this->getRequest()->getParam('what3words');
        $isoCode = $this->getRequest()->getParam('iso');
        // Make sure we have an API key
        $apiKey = Mage::helper('what3words/config')->getApiKey();
        if ($apiKey) {
            $options = array(
                'key' => $apiKey,
                'timeout' => 10
            );
            // Create an instance of the geocoder
            $geocoder = new \What3words\Geocoder\Geocoder($options);
            $payload = $geocoder->forwardGeocode($what3Words);
            $countryPayload = $this->validateCountry($what3Words, $geocoder, $isoCode);

            if (!$countryPayload['what3words']) {
                return $this->_returnJson(
                    array(
                        'success' => false,
                        'country' => false,
                        'message' => $countryPayload['message']
                    )
                );
            }

            //Analyse the payload
            $payloadArr = json_decode($payload, true);

            // If crs is set, it means we have a valid location
            if (isset($payloadArr['crs'])) {
                return $this->_returnJson(
                    array(
                    'success' => true,
                    'payload' => $payloadArr
                    )
                );
            }
            if (isset($payloadArr['status'])) {
                //Otherwise send the error message through
                return $this->_returnJson(
                    array(
                        'success' => false,
                        'message' => Mage::helper('what3words')
                            ->__($payloadArr['status']['message'])
                    )
                );
            } else {
                return $this->_returnJson(
                    array(
                        'success' => false,
                        'message' => Mage::helper('what3words')
                            ->__('An error occurred while validating the chosen three word address.')
                    )
                );
            }
        }
        return $this->_returnJson(
            array(
                'success' => false,
                'message' => Mage::helper('what3words')
                    ->__('Could not connect to what3words to validate')
            )
        );
    }

    /**
     * Check that the 3word address inputted matches the country selected
     * @param $what3words
     * @param $geocoder
     * @param $iso
     * @return array|mixed
     */
    private function validateCountry($what3words, $geocoder, $iso)
    {
        try {
            $payload = $geocoder->autoSuggest($what3words);
            $payloadArray = $payloadArr = json_decode($payload, true);

            $returnedWords = $payloadArray['suggestions'][0]['words'];
            $matchedIso = $payloadArray['suggestions'][0]['country'];

            if ($returnedWords === $what3words) {
                if ($matchedIso === strtolower($iso)) {
                    return array(
                        'what3words' => true,
                        'message' => $payloadArray
                    );
                } else {
                    return array(
                        'what3words' => false,
                        'message' => Mage::helper('what3words')->__(
                            'The 3 word address you have entered is not in the country selected above. Please re-enter or edit your 3 word address, or change the country.'
                        )
                    );
                }
            }
        } catch (Exception $e) {
            return array(
                'what3words' => false,
                'message' => Mage::helper('what3words')->__('Unable to connect to what3words to validate.')
            );
        }

        return $payloadArray;
    }

    /**
     * Get 3 word address
     * @return Zend_Controller_Response_Abstract
     */
    public function getAddressAction()
    {
        $addressId = $this->getRequest()->getParam('addressId');
        if (isset($addressId)) {
            $helper = Mage::helper('what3words');
            if ($threeWordAddress = $helper->getW3wByAddressId($addressId)) {
                return $this->_returnJson(
                    array(
                        'success' => true,
                        'what3words' => $threeWordAddress['w3w']
                    )
                );
            }
        }
        return $this->_returnJson(
            array(
                'success' => false
            )
        );
    }

    /**
     * Return JSON to the body
     * @param $json
     * @return Zend_Controller_Response_Abstract
     */
    private function _returnJson($json)
    {
        return $this->getResponse()
            ->setBody(
                Mage::helper('core')->jsonEncode($json)
            );
    }
}
