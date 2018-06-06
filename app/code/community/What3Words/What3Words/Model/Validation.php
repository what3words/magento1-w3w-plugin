<?php

/**
 * Class What3Words_What3Words_Model_Validation
 * @author Vicki Tingle
 */
class What3Words_What3Words_Model_Validation
{
    /**
     * Run validation with w3w PHP wrapper
     * @param $threeWordAddress
     * @param $iso
     * @return array
     */
    public function validate($threeWordAddress, $iso)
    {
        $apiKey = Mage::helper('what3words/config')->getApiKey();
        if ($apiKey) {
            $options = array(
                'key' => $apiKey,
                'timeout' => 10
            );
            // Create an instance of the geocoder
            $geocoder = new \What3words\Geocoder\Geocoder($options);
            $payload = $geocoder->forwardGeocode($threeWordAddress);

            $countryPayload = $this->validateCountry(
                $threeWordAddress,
                $geocoder,
                $iso
            );

            if (!$countryPayload['what3words']) {
                return array(
                    'success' => false,
                    'country' => false,
                    'message' => $countryPayload['message']
                );
            }

            $payloadArr = json_decode($payload, true);

            // If crs is set, it means we have a valid location
            if (isset($payloadArr['crs'])) {
                return array(
                    'success' => true,
                    'payload' => $payloadArr
                );
            }
            if (isset($payloadArr['status'])) {
                //Otherwise send the error message through
                return array(
                    'success' => false,
                    'message' => Mage::helper('what3words')
                        ->__($payloadArr['status']['message'])
                );
            } else {
                return array(
                    'success' => false,
                    'message' => Mage::helper('what3words')
                        ->__('An error occurred while validating the chosen three word address.')
                );
            }
        }
    }

    /**
     * @param $threeWordAddress string
     * @param $geocoder \What3words\Geocoder\Geocoder
     * @param $iso string
     * @return array|mixed
     */
    public function validateCountry($threeWordAddress, $geocoder, $iso)
    {
        try {
            $payload = $geocoder->autoSuggest($threeWordAddress);
            $payloadArray = $payloadArr = json_decode($payload, true);

            $returnedWords = $payloadArray['suggestions'][0]['words'];
            $matchedIso = $payloadArray['suggestions'][0]['country'];

            if ($returnedWords === $threeWordAddress) {
                if ($matchedIso === strtolower($iso)) {
                    return array(
                        'what3words' => true,
                        'message' => $payloadArray
                    );
                } else {
                    return array(
                        'what3words' => false,
                        'message' => Mage::helper('what3words')->__(
                            'The 3 word address entered was not in the country selected, so was not saved. Please edit the country to add a valid one.'
                        )
                    );
                }
            }
        } catch (Exception $e) {
            return array(
                'what3words' => false,
                'message' => Mage::helper('what3words')->__('The 3 word address: ' . $threeWordAddress . ' could not be validated, so was not saved.')
            );
        }

        return $payloadArray;
    }
}
