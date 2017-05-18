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
		$apikey = "JW1P2XNmXt4BkHVH";
	        $uid = 43;
	        $url = curl_init('https://www.rebatebus.com/api/applymidstream');

                $rebateitems = array();
		$shipdata = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData();	
		$billdata = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getData();	
        	$result = array();
		$amount = 0.0;
		$busid = "";
		Mage::log("doing save order", null, "rebatebus.log");
                foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item) {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
			Mage::log("in save order", null, "rebatebus.log");
			if ($rebate->getId()) {
				Mage::log("in save order got rebate item", null, "rebatebus.log");
				$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxqty()));
				$amount = $amount + $rebate->getAmount * min($item->getQty(), $rebate->getMaxqty());
				$busid = $rebate->getBusid();
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
			Mage::log("sending approval request", null, "rebatebus.log");
			$json = json_encode($postdata);
			curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($url, CURLOPT_POSTFIELDS, $json);
			curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($url, CURLOPT_HTTPHEADER, array(
				    'Content-Type: application/json',                                                                                
				    'Content-Length: ' . strlen($json)
			));
//			$context  = stream_context_create($options);
//			$response = file_get_contents($url, false, $context);
			$response = curl_exec($url);
			if (curl_error($url)) {
			    Mage::log("got error", null, "rebatebus.log");

			    $result['success'] = false;
			    $result['error'] = true;
			    $result['error_messages'] = $this->__('Error contacting utility incentive server - please remove the incentive, contact customer support, or try again later. Error: ' . curl_error($url));
			    $this->_prepareDataJSON($result);
			    curl_close($url);
			    return;
	
			}
			$jsondata = json_decode($response, true);
				
			Mage::log($response, null, "rebatebus.log");
			if ($jsondata->error) {
			    Mage::log("got error", null, "rebatebus.log");

			    $result['success'] = false;
			    $result['error'] = true;
			    $result['error_messages'] = $this->__('Error processing your utility incentive: ' . $jsondata->error);
			    $this->_prepareDataJSON($result);
			    curl_close($url);
			    return;
			}
			curl_close($url);
		}
		parent::saveOrderAction();


	}
}
?>
