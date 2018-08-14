<?php
namespace Bus\Rebate\Controller\Cart;

class ValidatePost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    protected $quoteRebate;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Variable\Model\VariableFactory $varFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Bus\Rebate\Model\RebateFactory $rebateFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Variable\Model\VariableFactory $varFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Bus\Rebate\Model\Quote\Rebate $quoteRebate,
        \Bus\Rebate\Model\RebateFactory $rebateFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
	\Psr\Log\LoggerInterface $logger	
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
	$this->cart = $cart;
	$this->logger = $logger;
        $this->quoteRebate = $quoteRebate;
	$this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->resultJsonFactory = $resultJsonFactory;
	$this->rebateFactory = $rebateFactory;
	$this->varFactory = $varFactory;
	$this->scopeConfig = $scopeConfig;
    }

    /**
     * Validate rebate
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
	$apikey = $this->varFactory->create()->loadByCode('rebatebus-apikey')->getPlainValue();
	$uid = $this->varFactory->create()->loadByCode('rebatebus-uid')->getPlainValue();
	
	$url = 'https://www.rebatebus.com/api/applymidstream';

	$rebateitems = array();
	$shipdata = $this->checkoutSession->getQuote()->getShippingAddress();
	$billdata = $this->checkoutSession->getQuote()->getBillingAddress();
	$result = array();
	$amount = 0.0;
	$busid = "";
	$price = 0;
	foreach ($this->cart->getQuote()->getAllItems() as $item) {
		$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); 
		if ($rebate->getAmount()) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				if ($rebate->getAmount()) {
					if ($item->getParentProductId() && $item->getParentItem()->getProduct()->getStockItem()->getProductTypeId() == 'configurable') {	
						$amount = $rebate->getAmount() * min($item->getQty(), $rebate->getMaxQty());
						$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxQty()), 'price' => $item->getParentItem()->getPrice(), 'rebateamount' => $amount);
					
					}
					else {
						$amount = $rebate->getAmount() * min($item->getQty(), $rebate->getMaxQty());
						$rebateitems[] = array('verification' => $rebate->getVerification(), 'quantity' => min($item->getQty(), $rebate->getMaxQty()), 'price' => $item->getPrice(), 'rebateamount' => $amount);
					}
					$busid = $rebate->getBusid();
				}
			}
		}
	}
	if (count($rebateitems)) {
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
		$postdata = array('zip' => $shipdata->getPostcode(),'billzip' => $billzip, 'address' => $shipdata->getStreet()[0] . "," . $shipdata->getCity() . "," . $shipdata->getRegion(),'billaddress' => $billaddress . "," . $billcity . ',' . $billregion, 'contactname' => $billfirstname . " " . $billlastname, 'contactphone' => $billtelephone, 'contactemail' => $this->getRequest()->getParam("email"), 'uid' => $uid, 'apikey' => $apikey, 'rebates' => $rebateitems, 'busid' => $busid, 'nosave' => true);
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
			    $result['error_message'] = 'Error processing your utility incentive: ' . $jsondata["error"];
			} 
			else if (strpos($http_response_header[0], "200") == false) {
			    $result['success'] = false;
			    $result['error'] = true;
			    $result['error_message'] = 'Error processing your utility incentive: ' . $http_response_header[0];
			}
			else {
			    $result['success'] = true;
			}
		} catch (Exception $e) {
			    $result['success'] = false;
			    $result['error'] = true;
			    $result['error_messages'] = 'Error processing your utility incentive: ' . $e->getMessage();
		}
      }
      else {
	$result['success'] = true;
      }
	return $this->resultJsonFactory->create()->setData($result);
    }
}


