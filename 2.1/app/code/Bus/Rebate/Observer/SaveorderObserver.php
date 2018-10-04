<?php
namespace Bus\Rebate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Customer;
use Magento\Sales\Api\Order\OrderRepositoryInterface;

class SaveorderObserver implements ObserverInterface
{
  
    public function __construct(
        \Magento\Variable\Model\VariableFactory $varFactory,
        \Bus\Rebate\Model\RebateFactory $rebateFactory,
          \Magento\Customer\Model\Customer $customer,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger	
    )
    {
        $this->logger = $logger;
        $this->quoteFactory = $quoteFactory;
        $this->orderRepository = $orderRepository;
        $this->varFactory = $varFactory;
        $this->customer = $customer;
        $this->rebateFactory = $rebateFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $this->orderRepository->get($observer->getEvent()->getOrderIds()[0]);
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());	
        $apikey = $this->varFactory->create()->loadByCode('rebatebus-apikey')->getPlainValue();
        $uid = $this->varFactory->create()->loadByCode('rebatebus-uid')->getPlainValue();
        
        $url = 'https://www.rebatebus.com/api/applymidstream';
      
        $rebateitems = array();
        $nonrebateitems = array();
        $shipdata = $order->getShippingAddress();
        $billdata = $order->getBillingAddress();
        $result = array();
        $amount = 0.0;
        $busid = "";
        $price = 0;
        foreach ($quote->getAllItems() as $item) {
            $rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); 
            if ($rebate->getAmount()) {
                if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
                        if ($item->getParentProductId() && $item->getParentItem()->getProductType() == 'configurable') {	
                            $amount = $rebate->getAmount() * min($item->getQty(), $rebate->getMaxQty());
                            $rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxQty()), 'price' => $item->getParentItem()->getPrice(), 'rebateamount' => $amount);
      
                        } else {
                            $amount = $rebate->getAmount() * min($item->getQty(), $rebate->getMaxQty());
                            $rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxQty()), 'price' => $item->getPrice(), 'rebateamount' => $amount);
                        }
                        $busid = $rebate->getBusid();
                }
            } else {
	       if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
                        if ($item->getParentProductId() && $item->getParentItem()->getProductType() == 'configurable') {	
                            $nonrebateitems[] = array('sku' => $item->getSku(), 'quantity' => $item->getQty(), 'price' => $item->getParentItem()->getPrice(), 
				'zip' => $shipdata->getPostcode(), 'apikey' => $apikey, 'uid' => $uid, 
				'address' => $shipdata->getStreet()[0] . ", " . $shipdata->getCity() . ", " . $shipdata->getRegion());
      
                        } else {
                            $nonrebateitems[] = array('sku' => $item->getSku(), 'quantity' => $item->getQty(), 'price' => $item->getPrice(), 
				'zip' => $shipdata->getPostcode(), 'apikey' => $apikey, 'uid' => $uid, 
				'address' => $shipdata->getStreet()[0] . ", " . $shipdata->getCity() . ", " . $shipdata->getRegion());
                        }
                }
            }
	}
        if (count($rebateitems)) {
            $this->logger->info("in save order observer with rebate items");
            $billzip = $shipdata->getPostcode();
            $billstreet = $shipdata->getStreet()[0];
            $billcity = $shipdata->getCity();
            $billregion = $shipdata->getRegion();
            $billfirstname = $shipdata->getFirstname();
            $billlastname = $shipdata->getLastname();
            $billtelephone = $shipdata->getTelephone();
            if (sizeof($billdata->getStreet())) { $billaddress = $billdata->getStreet()[0]; }
            if (strlen($billdata->getPostcode())) { $billzip = $billdata->getPostcode(); }
            if (strlen($billdata->getFirstname())) { $firstname = $billdata->getFirstname(); }
            if (strlen($billdata->getLastname())) {	$lastname = $billdata->getLastname(); }
            if (strlen($billdata->getRegion())) { $region = $billdata->getTelephone(); }
            if (strlen($billdata->getCity())) { $city = $billdata->getTelephone(); }
            $postdata = array('zip' => $shipdata->getPostcode(),'billzip' => $billzip, 'address' => $shipdata->getStreet()[0] . "," . $shipdata->getCity() . "," . $shipdata->getRegion(),'billaddress' => $billaddress . "," . $billcity . ',' . $billregion, 'contactname' => $billfirstname . " " . $billlastname, 'contactphone' => $billtelephone, 'contactemail' => $order->getCustomerEmail(), 'uid' => $uid, 'apikey' => $apikey, 'rebates' => $rebateitems, 'busid' => $busid);
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
            try {
                $jsondata = json_decode($response, true);

                if ($jsondata["error"]) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_message'] = __METHOD__ . '4 Error processing your utility incentive: ' . $jsondata["error"];
                } else if (strpos($http_response_header[0], "200") == false) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_message'] = __METHOD__ . '5 Error processing your utility incentive: ' . $http_response_header[0];
                } else {
                    $result['success'] = true;
                }
            } catch (Exception $e) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = __METHOD__ . '6 Error processing your utility incentive: ' . $e->getMessage();
            }
        } else {
	    if (count($nonrebateitems)) {
		$url = 'https://www.rebatebus.com/api/nonrebatesale';
		foreach ($nonrebateitems as $sale) {
			$options = array(
			    'http' => array( 
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'PUT',
				'ignore_errors' => true,
				'content' => http_build_query($sale)
			    )
			);
			$context  = stream_context_create($options);
			try {
				$response = file_get_contents($url, false, $context);
				$jsondata = json_decode($response, true);
				if ($jsondata["error"]) {
                	  	    $result['error_messages'] = __METHOD__ . '6 Error processing sale report: ' . $jsondata['error'];
				}  
				if (strpos($http_response_header[0], "200") == false) {
                	  	    $result['error_messages'] = __METHOD__ . '6 Error processing sale report: ' . $http_response_header[0];
				}
			} catch (Exception $e) {
                	  $result['error_messages'] = __METHOD__ . '6 Error processing sale report: ' . $e->getMessage();
			}
		}
	    }
        }
    }
}
