<?php
namespace Bus\Rebate\Controller\Cart;

class RebatePost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
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
        \Magento\Checkout\Model\Cart $cart,
        \Bus\Rebate\Model\RebateFactory $rebateFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
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
        $this->rebateFactory = $rebateFactory;
	$this->resultJsonFactory = $resultJsonFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize rebate
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
	if ($this->getRequest()->getParam("remove")) {
		$cartItems = $this->cart->getQuote()->getAllItems();
		foreach ($cartItems as $item) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
				if ($rebate->getAmount()) {
					    $this->messageManager->addSuccess(
						__(
						    'Incentive was Removed'
						)
					    );
					$rebate->delete();
				}
					
				if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
					$parentitem = $item->getParentItem();
					$parentitem->setDiscountAmount(0);
					$parentitem->setBaseDiscountAmount(0);
					$parentitem->setRebate(0);
				}
				$item->setRebate(0);
				$item->setDiscountAmount(0);
				$item->setBaseDiscountAmount(0);
			}
		}
		$this->_checkoutSession->getQuote()->collectTotals()->save();
	        return $this->_goBack();

	} else {
		$productId = strtolower((string) $this->getRequest()->getParam('product'));
		$verification = (string) $this->getRequest()->getParam('verification');
		$maxqty = (int) $this->getRequest()->getParam('maxqty');
		$amount = (float) $this->getRequest()->getParam('amount');
		$mincontribution = (float) $this->getRequest()->getParam('mincustomercontribution');
		$invoiceitemname = (string) $this->getRequest()->getParam('invoiceitemname');
		$program = (string) $this->getRequest()->getParam('program');
		$busid = (string) $this->getRequest()->getParam('busid');
		$cap = (float) $this->getRequest()->getParam('cap');
		$quote = $this->cart->getQuote();
		// No reason continue with empty shopping cart
		if (!$quote->getItemsCount()) {
	            return $this->_goBack();
		}
		foreach ($quote->getAllItems() as $item) {
			$this->logger->info("rebatepost, looking for ID " . $productId . " checking for rebate " . $item->getSku() . ", " . $item->getProductType());
			if (($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') && strtolower($item->getSku()) == $productId) {
				$model = $this->rebateFactory->create();
				if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
					if ($mincontribution && ($item->getParentItem()->getPrice() - $amount) < $mincontribution)
						$amount = $item->getParentItem()->getPrice() - $mincontribution;
					$item->getParentItem()->setRebate(0);
				} 
				else {
					if ($mincontribution && ($item->getPrice() - $amount) < $mincontribution)
						$amount = $item->getParentItem()->getPrice() - $mincontribution;
				}
				$model->setAmount($amount);
				$model->setVerification($verification);
				$model->setMaxQty($maxqty);
				$model->setProgram($program);
				$model->setItemId($item->getId());	
				$model->setInvoiceItemName($invoiceitemname);
				$model->setMincontribution($mincontribution);
				$model->setBusid($busid);
				$model->setCap($cap);
				$model->save();
				    $this->messageManager->addSuccess(
					__(
					    '$%1 %2 for %3 Was Applied.',
					    $amount,
					    $invoiceitemname,
					    $item->getName()
					)
				    );
				$this->_checkoutSession->getQuote()->collectTotals()->save();
        			return $this->_goBack();
			}
		}
		    $this->messageManager->addError(
			__(
			    'Rebate for product %1 not found in cart.',
			    $productId
			)
		    );
        	return $this->_goBack();
	}
    }
}


