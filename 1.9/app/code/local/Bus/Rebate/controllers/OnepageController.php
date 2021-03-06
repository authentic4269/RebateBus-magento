<?php 
require_once 'Mage/Checkout/controllers/OnepageController.php';
class Bus_Rebate_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * Add rebate
     */
    public function saveOrderAction()
    {
	// BEGIN Rebate Confirm Section
		$apikey = "YOUR_API_KEY";
	        $uid = YOUR_UID;
//		$apikey = "ZpHmFYvSgiFnc1NQ";
//		$uid = 1;
	        $url = 'https://www.rebatebus.com/api/applymidstream';

                $rebateitems = array();
		$shipdata = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData();	
		$billdata = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getData();	
        	$result = array();
		$amount = 0.0;
		$busid = "";
		$price = 0;
                foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item) {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				if ($rebate->getId()) {
	//				$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxqty()), 'price' => ($item->getPrice() - $rebate->getAmount()));
					if ($item->getParentProductId() && $item->getParentItem()->getProduct()->getStockItem()->getProductTypeId() == 'configurable') {	
						$amount = $rebate->getAmount() * min($item->getQty(), $rebate->getMaxqty());
						$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxqty()), 'price' => $item->getParentItem()->getPrice(), 'rebateamount' => $amount);
					
					}
					else {
						$amount = $rebate->getAmount() * min($item->getQty(), $rebate->getMaxqty());
						$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxqty()), 'price' => $item->getPrice(), 'rebateamount' => $amount);
					}
					$busid = $rebate->getBusid();
				}
			}
                }
		if (count($rebateitems)) {
			$postdata = array('zip' => $shipdata['postcode'],'billzip' => $billdata['postcode'], 'address' => $shipdata['street'] . "," . $shipdata['city'] . "," . $shipdata['region'],'billaddress' => $billdata['street'] . "," . $billdata['city'] . ',' . $billdata['region'], 'contactname' => $billdata['firstname'] . $billdata['lastname'], 'contactphone' => $billdata['telephone'], 'contactemail' => $billdata['email'], 'uid' => $uid, 'apikey' => $apikey, 'rebates' => $rebateitems, 'busid' => $busid);
			$options = array(
			    'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'ignore_errors' => true,
				'content' => http_build_query($postdata)
			    )
			);
			/*	
			$json = json_encode($postdata);
			curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($url, CURLOPT_POSTFIELDS, $json);
			curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($url, CURLOPT_HTTPHEADER, array(
				    'Content-Type: application/json',                                                                                
				    'Content-Length: ' . strlen($json)
			));
			*/
			$context  = stream_context_create($options);
			$response = file_get_contents($url, false, $context);
//			$response = curl_exec($url);
/*			if (curl_error($url)) {
			    Mage::log("got error", null, "rebatebus.log");

			    $result['success'] = false;
			    $result['error'] = true;
			    $result['error_messages'] = $this->__('Error contacting utility incentive server - please remove the incentive, contact customer support, or try again later. Error: ' . curl_error($url));
			    $this->_prepareDataJSON($result);
			    curl_close($url);
			    return;
	
			}
*/
			try {
				$jsondata = json_decode($response, true);
					
				if ($jsondata["error"]) {
				    $result['success'] = false;
				    $result['error'] = true;
				    $result['error_messages'] = $this->__('Error processing your utility incentive: ' . $jsondata["error"]);
				    $this->_prepareDataJSON($result);
				    return;
				} 
				if (strpos($http_response_header[0], "200") == false) {
				    Mage::log("got error", null, "rebatebus.log");
				    $result['success'] = false;
				    $result['error'] = true;
				    $result['error_messages'] = $this->__('Error processing your utility incentive: ' . $http_response_header[0]);
				    $this->_prepareDataJSON($result);
				    return;

				}
			} catch (Exception $e) {
				    $result['success'] = false;
				    $result['error'] = true;
				    $result['error_messages'] = $this->__('Error processing your utility incentive: ' . $e->getMessage());
	
			}
//			curl_close($url);
		}
		parent::saveOrderAction();


	}
}
?>
