<?php
namespace Bus\Rebate\Model\Quote;

class Rebate extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
	* @param \Magento\Framework\Event\ManagerInterface $eventManager,
	* @param \Magento\Store\Model\StoreManagerInterface $storeManager,
	* @param \Magento\SalesRule\Model\Validator $validator,
	* @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
	* @param \Bus\Rebate\Model\RebateFactory $rebateFactory,
	* @param \Psr\Log\LoggerInterface $logger	
     */
	 public function __construct(
	 \Magento\Framework\Event\ManagerInterface $eventManager,
	 \Magento\Store\Model\StoreManagerInterface $storeManager,
	 \Magento\SalesRule\Model\Validator $validator,
	 \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
         \Bus\Rebate\Model\RebateFactory $rebateFactory,
	 \Psr\Log\LoggerInterface $logger	
	 ) 
		 {
		 $this->setCode('busrebate');
		 $this->eventManager = $eventManager;
		 $this->calculator = $validator;
		 $this->storeManager = $storeManager;
		 $this->priceCurrency = $priceCurrency;
		$this->rebateFactory = $rebateFactory;
		$this->logger = $logger;
	 }
	 public function collect(
	 \Magento\Quote\Model\Quote $quote,
	 \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
	 \Magento\Quote\Model\Quote\Address\Total $total
	 )
	 {
		$items = $shippingAssignment->getItems();
		if (!count($items))
			return $this;
		 parent::collect($quote, $shippingAssignment, $total);
		$label = '';
		$totalRebateAmount = 0;
		$result = null;
		foreach ($quote->getAllItems() as $item) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
				if ($rebate->getAmount()) {
				    $rebateAmount = 0;
				    $qty = 0;
				    $price = 0;
				    $label = $rebate->getInvoiceItemName();			
				    if ($item->getParentItemId() && $item->getParentItem()->getProduct()->getStockItem()->getProductTypeId() == 'configurable') {
					$qty = $item->getParentItem()->getQty();
					$price = $item->getParentItem()->getPrice();
				    } else {
					$qty = $item->getQty();
					$price = $item->getPrice();
				    }
				    if ($rebate->getMaxQty() < $qty) {
					$qty = $rebate->getMaxQty();
				    }
				    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$rebateAmount = ($rebate->getCap() / 100.0) * $qty;	
				    }
				    else {
					$rebateAmount = $rebate->getAmount() * $qty;	
				    }
				    if ($rebateAmount < $qty * $rebate->getMinContribution()) {
					$rebateAmount = $qty * $rebate->getMinContribution();
				    }
				    $totalRebateAmount += $rebateAmount;
				}
			}
		}
		 
		 $discountAmount ="-".$totalRebateAmount; 
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
		$label = '';
		$totalRebateAmount = 0;
		$result = null;
		foreach ($quote->getAllItems() as $item) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
				if ($rebate->getAmount()) {
				    $rebateAmount = 0;
				    $qty = 0;
				    $price = 0;
				    $label = $rebate->getInvoiceItemName();			
				    if ($item->getParentItemId() && $item->getParentItem()->getProduct()->getStockItem()->getProductTypeId() == 'configurable') {
					$qty = $item->getParentItem()->getQty();
					$price = $item->getParentItem()->getPrice();
				    } else {
					$qty = $item->getQty();
					$price = $item->getPrice();
				    }
				    if ($rebate->getMaxQty() < $qty) {
					$qty = $rebate->getMaxQty();
				    }
				    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$rebateAmount = ($rebate->getCap() / 100.0) * $qty;	
				    }
				    else {
					$rebateAmount = $rebate->getAmount() * $qty;	
				    }
				    if ($rebateAmount < $qty * $rebate->getMinContribution()) {
					$rebateAmount = $qty * $rebate->getMinContribution();
				    }
				    $totalRebateAmount += $rebateAmount;
				}
			}
		}
		 if ($totalRebateAmount != 0)
		 { 
			$label = "";
			 $result = [
			 'code' => 'busrebate',
			 'title' => $label,
			 'value' => $total->getDiscountAmount() 
			 ];
		 }

		 return $result;
	 }

}
