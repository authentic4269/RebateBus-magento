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
		$priorDiscount = $total->getDiscountAmount();
		foreach ($quote->getAllItems() as $item) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
				if ($rebate->getAmount()) {
				    $rebateAmount = 0;
				    $qty = 0;
				    $price = 0;
				    $label = $rebate->getInvoiceItemName();			
				    $itemPriorDiscount = 0;
				    $parentitem = null;
				    if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
					$parentitem = $item->getParentItem();
					$qty = $parentitem->getQty();
					$price = $parentitem->getPrice() - $parentitem->getDiscountAmount() / $qty;
					$itemPriorDiscount = $parentitem->getDiscountAmount();
				    } else {
					$qty = $item->getQty();
					$price = $item->getPrice() - $item->getDiscountAmount() / $qty;
					$itemPriorDiscount = $item->getDiscountAmount();
				    }
				    $this->logger->info("in quote rebates, item prior discount " . $itemPriorDiscount);
				    if ($rebate->getMaxQty() < $qty) {
					$qty = $rebate->getMaxQty();
				    }
				    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$rebateAmount = ($rebate->getCap() / 100.0) * $qty * $price;	
				    }
				    else {
					$rebateAmount = $rebate->getAmount() * $qty;	
				    }
				    if ($rebateAmount < $qty * $rebate->getMinContribution()) {
					$rebateAmount = $qty * $rebate->getMinContribution();
				    }
				    $totalRebateAmount += $rebateAmount;
				    $item->setDiscountAmount($rebateAmount + $itemPriorDiscount);
				    $item->setBaseDiscountAmount($rebateAmount + $itemPriorDiscount);
				    if ($parentitem != null) {
					    $parentitem->setDiscountAmount($rebateAmount + $itemPriorDiscount);
					    $parentitem->setBaseDiscountAmount($rebateAmount + $itemPriorDiscount);
				    	    $parentitem->setRebate($rebateAmount);
				    }
				    $this->logger->info("in quote rebates, setting total item discount " . ($rebateAmount + $itemPriorDiscount));
	
				    $item->setRebate($rebateAmount);
				}
			}
		}
		if ($totalRebateAmount != 0) {	 
			 $discountAmount ="-".$totalRebateAmount; 
			 $discountAmount = $discountAmount + $priorDiscount;
			 $appliedCartDiscount = 0;
			 
			 //$quote->setCouponCode($label);
			 //$total->setCouponCode($label);
			 if ($priorDiscount) {
				 $total->setDiscountDescription($label . ", Promo Code: " . $total->getDiscountDescription());
				 $quote->setDiscountDescription($label . ", Promo Code: " . $total->getDiscountDescription());
			 } else {
				 $total->setDiscountDescription($label);
				 $quote->setDiscountDescription($label);
			 }
			 $total->setDiscountAmount($discountAmount);
			 $total->setBaseDiscountAmount($discountAmount);
			 //$total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
			 //$total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);
			 
			 $total->addTotalAmount($this->getCode(), -$totalRebateAmount);
			 $total->addBaseTotalAmount($this->getCode(), -$totalRebateAmount);
			 return $this;
		}
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
				    if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
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
			$this->logger->info("in fetch quote rebates, total rebate " . $totalRebateAmount);
			 $result = [
			 'code' => 'busrebate',
			 'title' => $label,
			 'value' => -$totalRebateAmount
			 ];
		 }

		 return $result;
	 }

}
