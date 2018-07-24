<?php
namespace Bus\Rebate\Model\Quote;

class Rebate extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
	 public function __construct(
	 \Magento\Framework\Event\ManagerInterface $eventManager,
	 \Magento\Store\Model\StoreManagerInterface $storeManager,
	 \Magento\SalesRule\Model\Validator $validator,
	 \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
	\Psr\Log\LoggerInterface $logger	
	 ) 
		 {
		 $this->setCode('busrebate');
		 $this->eventManager = $eventManager;
		 $this->calculator = $validator;
		 $this->storeManager = $storeManager;
		 $this->priceCurrency = $priceCurrency;
		$this->logger = $logger;
	 }
	 
	 public function collect(
	 \Magento\Quote\Model\Quote $quote,
	 \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
	 \Magento\Quote\Model\Quote\Address\Total $total
	 )
	 {
		 parent::collect($quote, $shippingAssignment, $total);
		 $address = $shippingAssignment->getShipping()->getAddress();
		 $label = 'My Custom Discount';
		 $TotalAmount=$total->getSubtotal();
		 $TotalAmount=$TotalAmount/10; //Set 10% discount
		$this->logger->info("Got to calculate custom discount"); 
		 $discountAmount ="-".$TotalAmount; 
		 $appliedCartDiscount = 0;
		 
		if($total->getDiscountDescription())
		 {
		 $appliedCartDiscount = $total->getDiscountAmount();
		 $discountAmount = $total->getDiscountAmount()+$discountAmount;
		 $label = $total->getDiscountDescription().', '.$label;
		 } 
		 
		 $total->setDiscountDescription($label);
		 $total->setDiscountAmount($discountAmount);
		 $total->setBaseDiscountAmount($discountAmount);
		 $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
		 $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);
		 
		 if(isset($appliedCartDiscount))
		 {
		 $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
		 $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
		 } 
		 else 
		 {
		 $total->addTotalAmount($this->getCode(), $discountAmount);
		 $total->addBaseTotalAmount($this->getCode(), $discountAmount);
		 }
		 return $this;
	 }
	 
	 public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
	 {
	 $result = null;
	 $amount = $total->getDiscountAmount();
	 
	 if ($amount != 0)
	 { 
	 $description = $total->getDiscountDescription();
	 $result = [
//	 'code' => $this->getCode(),
	 'code' => 'busrebate',
	 'title' => strlen($description) ? __('Discount (%10)', $description) : __('Discount'),
	 'value' => $amount
	 ];
	 }
	 return $result;
	 }
}
