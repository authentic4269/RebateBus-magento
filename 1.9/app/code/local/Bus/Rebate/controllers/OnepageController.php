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
	        $url = 'https://www.rebatebus.com/api/applymidstream';

                $rebateitems = array();
		$shipdata = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getData();	
		$billdata = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getData();	
        	$result = array();
		$amount = 0.0;

                foreach (Mage::getSingleton('checkout/session')->getQuote()->getAllItems() as $item) {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
			if ($rebate->getId()) {
				$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxqty()));
				$amount = $amount + $rebate->getAmount * min($item->getQty(), $rebate->getMaxqty());
			}
                }
		if (count($rebateitems)) {
			$postdata = array('zip' => $shipdata['postcode'],'billzip' => $billdata['postcode'], 'address' => $shipdata['street'] . "," . $shipdata['city'] . "," . $shipdata['region'],'billaddress' => $billdata['street'] . "," . $billdata['city'] . ',' . $billdata['region'], 'contactname' => $billdata['firstname'] . $billdata['lastname'], 'contactphone' => $billdata['telephone'], 'contactemail' => $billdata['email'], 'uid' => 43, 'apikey' => $apikey, 'rebates' => $rebateitems);
			$options = array(
			    'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'ignore_errors' => true,
				'content' => http_build_query($postdata)
			    )
			);
			$context  = stream_context_create($options);
			$response = file_get_contents($url, false, $context);
			$jsondata = json_decode($response);

			if ($jsondata->error) {

			    $result['success'] = false;
			    $result['error'] = true;
			    $result['error_messages'] = $this->__('Error processing your utility incentive: ' . $jsondata->error);
			    $this->_prepareDataJSON($result);
			    return;
			}
		}
		parent::saveOrderAction();


	}
}
?>
