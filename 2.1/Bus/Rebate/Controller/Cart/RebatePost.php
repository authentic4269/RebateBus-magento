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
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
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
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->rebateFactory = $rebateFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
	if ($this->getRequest()->getParam("remove")) {
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$cartItems = $quote->getAllItems();
		foreach ($cartItems as $item) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				$rebate = $this->rebateFactory->create()->getRebateByItemId($item->getId());
				if ($rebate) {
					$this->_getSession()->addSuccess(
						'Incentive Removed'
					);
					$rebate->delete();
				}
			}
		}
	        return $this->_goBack();

	} else {
		$productId = (string) $this->getRequest()->getParam('product');
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
			if (($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') && $item->getSku() == $productId) {
				$model = $this->rebateFactory->create();
				if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
					if ($amount > $item->getParentItem()->getPrice() * ($cap / 100.0))
						$amount = $item->getParentItem()->getPrice() * ($cap / 100.0);
					if ($mincontribution && ($item->getParentItem()->getPrice() - $amount) < $mincontribution)
						$amount = $item->getParentItem()->getPrice() - $mincontribution;
				} 
				else {
					if ($amount > $item->getPrice() * ($cap / 100.0))
						$amount = $item->getPrice() * ($cap / 100.0);
					if ($mincontribution && ($item->getPrice() - $amount) < $mincontribution)
						$amount = $item->getParentItem()->getPrice() - $mincontribution;
				}
				$model->setAmount($amount);
				$model->setVerification($verification);
				$model->setMaxqty($maxqty);
				$model->setProgram($program);
				$model->setItemId($item->getId());	
				$model->setInvoiceItemName($invoiceitemname);
				$model->setMincontribution($mincontribution);
				$model->setBusid($busid);
				$model->setCap($cap);
				$model->save();
				    $this->messageManager->addSuccess(
					__(
					    '%1 for %2 Was Applied.',
					    $escaper->escapeHtml($invoiceitemname),
					    $escaper->escapeHtml($item->getName())
					)
				    );
 
			}
		}
		$this->_getSession()->addError('Rebate for product %s not found in cart', Mage::helper('core')->escapeHtml($productId));
		    $this->messageManager->addError(
			__(
			    'Rebate for product %1 not found in cart.',
			    $escaper->escapeHtml($productId)
			)
		    );
 

        	return $this->_goBack();
	}
}


/*

        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get('Magento\Framework\Escaper');
                if (!$itemsCount) {
                    if ($isCodeLengthValid) {
                        $coupon = $this->couponFactory->create();
                        $coupon->load($couponCode, 'code');
                        if ($coupon->getId()) {
                            $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                            $this->messageManager->addSuccess(
                                __(
                                    'You used coupon code "%1".',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        } else {
                            $this->messageManager->addError(
                                __(
                                    'The coupon code "%1" is not valid.',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        }
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccess(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $this->cart->save();
                    }
                }
            } else {
                $this->messageManager->addSuccess(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the coupon code.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
*/
