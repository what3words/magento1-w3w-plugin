<?php

require_once(Mage::getModuleDir('controllers','Aramex_Shipment') . DS . 'ShipmentController.php');

class What3Words_What3Words_Aramex_ShipmentController extends Aramex_Shipment_ShipmentController
{
    public function postAction()
    {
        $baseUrl = Mage::helper('aramexshipment')->getWsdlPath();
        //SOAP object
        $soapClient = new SoapClient($baseUrl . 'shipping.wsdl');
        $aramex_errors = false;
        $post = $this->getRequest()->getPost();

        $flag = true;
        $error = "";
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            // Form processing
            $order = Mage::getModel('sales/order')->loadByIncrementId($post['aramex_shipment_original_reference']);
            $payment = $order->getPayment();

            $totalWeight = 0;
            $totalItems = 0;
            $items = $order->getAllItems();
            $aramexItemsCounter = 0;

            foreach ($post['aramex_items'] as $key => $value) {
                $aramexItemsCounter++;
                if ($value != 0) {
                    // Iterating order items
                    foreach ($items as $item) {
                        if ($item->getId() == $key) {
                            // Get weight
                            if ($item->getWeight() != 0) {
                                $weight =  $item->getWeight()*$item->getQtyOrdered();
                            } else {
                                $weight =  0.5*$item->getQtyOrdered();
                            }

                            // collect items for aramex
                            $aramex_items[] = array(
                                'PackageType' => 'Box',
                                'Quantity' => $post[$item->getId()],
                                'Weight' => array(
                                    'Value' => $weight,
                                    'Unit' => 'Kg'
                                ),
                                'Comments' => $item->getName(),
                                'Reference' => ''
                            );
                            $totalWeight += $weight;
                            $totalItems += $post[$item->getId()];
                        }
                    }
                }
            }

            $aramex_atachments = array();
            //attachment
            for ($i = 1; $i <= 3; $i++) {
                $fileName = $_FILES['file'.$i]['name'];
                if (isset($fileName) != '') {
                    $fileName = explode('.', $fileName);
                    $fileName = $fileName[0]; //filename without extension
                    $fileData ='';
                    if ($_FILES['file'.$i]['tmp_name'] != '') {
                        $fileData = file_get_contents(
                            $_FILES['file'.$i]['tmp_name']
                        );
                    }
                    $ext = pathinfo($_FILES['file'.$i]['name'], PATHINFO_EXTENSION); //file extension
                    if ($fileName && $ext && $fileData) {
                        $aramex_atachments[] = array(
                            'FileName' => $fileName,
                            'FileExtension' => $ext,
                            'FileContents' => $fileData
                        );
                    }
                }
            }
            $totalWeight = $post['order_weight'];
            $params = array();

            //shipper parameters
            $params['Shipper'] = array(
                'Reference1' => $post['aramex_shipment_shipper_reference'], //'ref11111',
                'Reference2' => '',
                'AccountNumber' => ($post['aramex_shipment_info_billing_account'] == 1) ? $post['aramex_shipment_shipper_account'] : $post['aramex_shipment_shipper_account'], //'43871',

                //Party Address
                'PartyAddress' => array(
                    'Line1' => addslashes($post['aramex_shipment_shipper_street']), //'13 Mecca St',
                    'Line2' => '',
                    'Line3' => '',
                    'City' => $post['aramex_shipment_shipper_city'], //'Dubai',
                    'StateOrProvinceCode' => $post['aramex_shipment_shipper_state'], //'',
                    'PostCode' => $post['aramex_shipment_shipper_postal'],
                    'CountryCode' => $post['aramex_shipment_shipper_country'], //'AE'
                ),

                //Contact Info
                'Contact' => array(
                    'Department' => '',
                    'PersonName' => $post['aramex_shipment_shipper_name'], //'Suheir',
                    'Title' => '',
                    'CompanyName' => $post['aramex_shipment_shipper_company'], //'Aramex',
                    'PhoneNumber1' => $post['aramex_shipment_shipper_phone'], //'55555555',
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' => $post['aramex_shipment_shipper_phone'],
                    'EmailAddress' => $post['aramex_shipment_shipper_email'], //'',
                    'Type' => ''
                ),
            );

            $what3words = false;
            if ($w3wOrder = Mage::getModel('what3words/order')
                ->getCollection()
                ->loadByOrderId(
                    $order->getId()
                )
            ) {
                $what3words = $w3wOrder['w3w'];
            }

            //Consignee parameters
            $params['Consignee'] = array(
                'Reference1' => $post['aramex_shipment_receiver_reference'], //'',
                'Reference2' => $what3words ? $what3words: '',
                'AccountNumber' => ($post['aramex_shipment_info_billing_account'] == 2)? $post['aramex_shipment_shipper_account'] : '',

                //Party Address
                'PartyAddress' => array(
                    'Line1' => $post['aramex_shipment_receiver_street'], //'15 ABC St',
                    'Line2' => '',
                    'Line3' => '/// ' . $what3words ? $what3words : '',
                    'City' => $post['aramex_shipment_receiver_city'], //'Amman',
                    'StateOrProvinceCode' => '',
                    'PostCode' => $post['aramex_shipment_receiver_postal'],
                    'CountryCode' => $post['aramex_shipment_receiver_country'], //'JO'
                ),

                //Contact Info
                'Contact' => array(
                    'Department' => '',
                    'PersonName' => $post['aramex_shipment_receiver_name'], //'Mazen',
                    'Title' => '',
                    'CompanyName' => $post['aramex_shipment_receiver_company'], //'Aramex',
                    'PhoneNumber1' => $post['aramex_shipment_receiver_phone'], //'6666666',
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' =>  $post['aramex_shipment_receiver_phone'],
                    'EmailAddress' => $post['aramex_shipment_receiver_email'], //'mazen@aramex.com',
                    'Type' => ''
                )
            );

            if ($post['aramex_shipment_info_billing_account'] == 3) {
                $params['ThirdParty'] = array(
                    'Reference1' => $post['aramex_shipment_shipper_reference'], //'ref11111',
                    'Reference2' => '',
                    'AccountNumber' => $post['aramex_shipment_shipper_account'], //'43871',

                    //Party Address
                    'PartyAddress' => array(
                        'Line1' => addslashes(Mage::getStoreConfig('aramexsettings/shipperdetail/address')), //'13 Mecca St',
                        'Line2' => '',
                        'Line3' => '',
                        'City' => Mage::getStoreConfig('aramexsettings/shipperdetail/city'), //'Dubai',
                        'StateOrProvinceCode' => Mage::getStoreConfig('aramexsettings/shipperdetail/state'), //'',
                        'PostCode' => Mage::getStoreConfig('aramexsettings/shipperdetail/postalcode'),
                        'CountryCode' => Mage::getStoreConfig('aramexsettings/shipperdetail/country'), //'AE'
                    ),

                    //Contact Info
                    'Contact' => array(
                        'Department' => '',
                        'PersonName' => Mage::getStoreConfig('aramexsettings/shipperdetail/name'), //'Suheir',
                        'Title' => '',
                        'CompanyName' => Mage::getStoreConfig('aramexsettings/shipperdetail/company'), //'Aramex',
                        'PhoneNumber1' => Mage::getStoreConfig('aramexsettings/shipperdetail/phone'), //'55555555',
                        'PhoneNumber1Ext' => '',
                        'PhoneNumber2' => '',
                        'PhoneNumber2Ext' => '',
                        'FaxNumber' => '',
                        'CellPhone' => Mage::getStoreConfig('aramexsettings/shipperdetail/phone'),
                        'EmailAddress' => Mage::getStoreConfig('aramexsettings/shipperdetail/email'), //'',
                        'Type' => ''
                    ),
                );

            }
            // Other Main Shipment Parameters
            $params['Reference1'] = $post['aramex_shipment_info_reference']; //'Shpt0001';
            $params['Reference2'] = '';
            $params['Reference3'] = '';
            $params['ForeignHAWB'] = $post['aramex_shipment_info_foreignhawb'];

            $params['TransportType'] = 0;
            $params['ShippingDateTime'] = time(); //date('m/d/Y g:i:sA');
            $params['DueDate'] = time() + (7 * 24 * 60 * 60); //date('m/d/Y g:i:sA');
            $params['PickupLocation'] = 'Reception';
            $params['PickupGUID'] = '';
            $params['Comments'] = $post['aramex_shipment_info_comment'];
            $params['AccountingInstrcutions'] = '';
            $params['OperationsInstructions'] = '';
            $params['Details'] = array(
                'Dimensions' => array(
                    'Length' => '0',
                    'Width' => '0',
                    'Height' => '0',
                    'Unit' => 'cm'
                ),

                'ActualWeight' => array(
                    'Value' => $totalWeight,
                    'Unit' => $post['weight_unit']
                ),

                'ProductGroup' => $post['aramex_shipment_info_product_group'], //'EXP',
                'ProductType' => $post['aramex_shipment_info_product_type'], //,'PDX'


                'PaymentType' => $post['aramex_shipment_info_payment_type'],


                'PaymentOptions' => $post['aramex_shipment_info_payment_option'], //$post['aramex_shipment_info_payment_option']


                'Services' => $post['aramex_shipment_info_service_type'],

                'NumberOfPieces' => $totalItems,
                'DescriptionOfGoods' => $post['aramex_shipment_description'],
                'GoodsOriginCountry' => $post['aramex_shipment_shipper_country'], //'JO',
                'Items' => $aramex_items,
            );
            if (count($aramex_atachments)) {
                $params['Attachments'] = $aramex_atachments;
            }

            $params['Details']['CashOnDeliveryAmount'] = array(
                'Value' => $post['aramex_shipment_info_cod_amount'],
                'CurrencyCode' => $post['aramex_shipment_currency_code']
            );

            $params['Details']['CustomsValueAmount'] = array(
                'Value' => $post['aramex_shipment_info_custom_amount'],
                'CurrencyCode' =>  $post['aramex_shipment_currency_code']
            );

            $major_par['Shipments'][] = $params;
            $clientInfo = Mage::helper('aramexshipment')->getClientInfo();
            $major_par['ClientInfo'] = $clientInfo;

            $report_id = (int) Mage::getStoreConfig('aramexsettings/config/report_id');
            if (!$report_id) {
                $report_id =9729;
            }

            $major_par['LabelInfo'] = array(
                'ReportID' => $report_id, //'9201',
                'ReportType' => 'URL'
            );


            $formSession = Mage::getSingleton('adminhtml/session');
            $formSession->setData("form_data",$post);
            try {
                //create shipment call
                $auth_call = $soapClient->CreateShipments($major_par);
                if ($auth_call->HasErrors) {
                    if (empty($auth_call->Shipments)) {
                        if (count($auth_call->Notifications->Notification) > 1) {
                            foreach ($auth_call->Notifications->Notification as $notify_error) {
                                Mage::throwException($this->__('Aramex: ' . $notify_error->Code .' - '. $notify_error->Message));
                            }
                        } else {
                            Mage::throwException($this->__('Aramex: ' . $auth_call->Notifications->Notification->Code . ' - '. $auth_call->Notifications->Notification->Message));
                        }
                    } else {
                        if (count($auth_call->Shipments->ProcessedShipment->Notifications->Notification) > 1) {
                            $notification_string = '';
                            foreach ($auth_call->Shipments->ProcessedShipment->Notifications->Notification as $notification_error) {
                                $notification_string .= $notification_error->Code .' - '. $notification_error->Message . ' <br />';
                            }
                            Mage::throwException($notification_string);
                        } else {
                            Mage::throwException($this->__('Aramex: ' . $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Code .' - '. $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Message));
                        }
                    }
                } else {
                    if ($order->canShip() && $post['aramex_return_shipment_creation_date']=="create") {

                        $shipmentid = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $post['aramex_items'], "AWB No. ".$auth_call->Shipments->ProcessedShipment->ID." - Order No. ".$auth_call->Shipments->ProcessedShipment->Reference1." - <a href='javascript:void(0);' onclick='myObj.printLabel();'>Print Label</a>");

                        $ship = true;

                        $ship = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentid, 'aramex', 'Aramex', $auth_call->Shipments->ProcessedShipment->ID);

                        /* sending mail */
                        if ($ship) {
                            /* if($post['aramex_email_customer'] == 'yes'){ */

                            /* send shipment mail */
                            $storeId = $order->getStore()->getId();
                            $copyTo = Mage::helper('aramex_core')->getEmails(self::	XML_PATH_SHIPMENT_EMAIL_COPY_TO,$storeId);
                            $copyMethod = Mage::getStoreConfig(self::XML_PATH_SHIPMENT_EMAIL_COPY_METHOD, $storeId);

                            $templateId = Mage::getStoreConfig(self::XML_PATH_SHIPMENT_EMAIL_TEMPLATE, $storeId);

                            if ($order->getCustomerIsGuest()) {
                                $customerName = $order->getBillingAddress()->getName();
                            } else {
                                $customerName = $order->getCustomerName();
                            }
                            $shipments_id = $auth_call->Shipments->ProcessedShipment->ID;

                            $mailer = Mage::getModel('core/email_template_mailer');

                            $emailInfo = Mage::getModel('core/email_info');
                            $emailInfo->addTo($order->getCustomerEmail(), $customerName);

                            if ($copyTo && $copyMethod == 'bcc') {
                                /* Add bcc to customer email  */
                                foreach ($copyTo as $email) {
                                    $emailInfo->addBcc($email);
                                }
                            }
                            $mailer->addEmailInfo($emailInfo);
                            /* Email copies are sent as separated emails if their copy method is 'copy' */
                            if ($copyTo && $copyMethod == 'copy') {
                                foreach ($copyTo as $email) {
                                    $emailInfo = Mage::getModel('core/email_info');
                                    $emailInfo->addTo($email);
                                    $mailer->addEmailInfo($emailInfo);
                                }
                            }

                            $senderName = Mage::getStoreConfig(self::XML_PATH_TRANS_IDENTITY_NAME,$storeId);
                            $senderEmail = Mage::getStoreConfig(self::XML_PATH_TRANS_IDENTITY_EMAIL,$storeId);

                            /* Set all required params and send emails */
                            $mailer->setSender(array('name' => $senderName, 'email' =>$senderEmail));
                            $mailer->setStoreId($storeId);
                            $mailer->setTemplateId($templateId);
                            $mailer->setTemplateParams(
                                array(
                                    'order'        => $order,
                                    'shipments_id' => $shipments_id
                                )
                            );
                            try {
                                $mailer->send();
                            }
                            catch(Exception $ex) {
                                Mage::getSingleton('core/session')
                                    ->addError('Unable to send email.');
                            }
                            /* } */
                        }

                        Mage::getSingleton('core/session')->addSuccess('Aramex Shipment Number: '.$auth_call->Shipments->ProcessedShipment->ID.' has been created.');
                        /* $order->setState('warehouse_pickup_shipped', true); */
                    } elseif ($post['aramex_return_shipment_creation_date'] == "return") {
                        $message = "Aramex Shipment Return Order AWB No. ".$auth_call->Shipments->ProcessedShipment->ID." - Order No. ".$auth_call->Shipments->ProcessedShipment->Reference1." - <a href='javascript:void(0);' onclick='myObj.printLabel();'>Print Label</a>";

                        Mage::getSingleton('core/session')->addSuccess('Aramex Shipment Return Order Number: '.$auth_call->Shipments->ProcessedShipment->ID.' has been created.');
                        $order->addStatusToHistory($order->getStatus(), $message, false);
                        $order->save();
                    } else {
                        Mage::throwException($this->__('Cannot do shipment for the order.'));
                    }
                }
            } catch (Exception $e) {
                $aramex_errors = true;
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }

            if ($aramex_errors) {
                $strip = strstr($post['aramex_shipment_referer'],"aramexpopup",true);
                $url = $strip;
                if (empty($strip)) {
                    $url=$post['aramex_shipment_referer'];
                }
                $this->_redirectUrl($url . 'aramexpopup/show');
            } else {
                $this->_redirectUrl($post['aramex_shipment_referer']);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}
