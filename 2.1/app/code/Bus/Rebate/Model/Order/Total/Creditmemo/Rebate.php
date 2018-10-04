<?php
namespace Bus\Rebate\Model\Order\Total\Creditmemo;

class Rebate extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
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
	 \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
	 \Psr\Log\LoggerInterface $logger	
	 ) 
		 {
		 $this->setCode('busrebate');
		 $this->eventManager = $eventManager;
		 $this->calculator = $validator;
		 $this->storeManager = $storeManager;
		 $this->priceCurrency = $priceCurrency;
		$this->rebateFactory = $rebateFactory;
		$this->quoteItemFactory = $quoteItemFactory;
		$this->logger = $logger;
	 }
	 public function collect(
	 \Magento\Sales\Model\Order\Creditmemo $memo
	 )
	 {
		$totalMemoDiscount = 0;
		$hasRebate = 0;
		$result = null;
		$gotrebate = 0;
		foreach ($memo->getAllItems() as $memoitem) {
			$orderitem = $memoitem->getOrderItem();
			$item = $this->quoteItemFactory->create()->load($orderitem->getQuoteItemId(), 'item_id');
			$gotrebate = 0;
			if ($orderitem->getProductType() == 'simple' || $orderitem->getProductType() == 'grouped' || $orderitem->getProductType() == 'virtual') {
				$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
				if ($rebate->getAmount()) {
				    $gotrebate = 1;
				    $hasRebate = 1;
				    $invoiceitemname = $rebate->getInvoiceItemName();
				    $rebateAmount = 0;
				    $qty_to_refund = $memoitem->getQty();
				    $full_qty_to_refund = $memoitem->getQty();
				    $qty_already_refunded = 0;
				    $orderqty = $orderitem->getQtyOrdered();
				    $price = 0;
				    $rebate_fullqty = 0;
				    $label = $rebate->getInvoiceItemName();			
				    $parent = null;
				    if ($orderitem->getParentItemId() && $orderitem->getParentItem()->getProductType() == "configurable") {
					$price = $orderitem->getParentItem()->getPrice() * (1.0 - ($orderitem->getParentItem()->getDiscountPercent() / 100.0));
				    	$qty_already_refunded = $orderitem->getParentItem()->getQtyRefunded();
					$parent_order_item_id = $orderitem->getParentItemId();
					$orderqty = $orderitem->getParentItem()->getQtyOrdered();
					foreach ($memo->getAllItems() as $possible_parent) {
						if ($possible_parent->getOrderItemId() == $parent_order_item_id) {
							$parent = $possible_parent;
							$qty_to_refund = $possible_parent->getQty();
				    			$full_qty_to_refund = $possible_parent->getQty();
							break;
						}
					}
	
				    } else {
				        $price = $orderitem->getPrice() * (1.0 - ($orderitem->getDiscountPercent() / 100.0));
				    	$qty_already_refunded = $orderitem->getQtyRefunded();
					$orderqty = $orderitem->getQtyOrdered();
				    }
				    $remaining_no_rebates_qty = $orderqty - $rebate->getMaxQty() - $qty_already_refunded;
				    $this->logger->info("remaining no-rebates qty " . $remaining_no_rebates_qty);
				    if ($remaining_no_rebates_qty > 0) {
					$qty_to_refund = max(0, $qty_to_refund - $remaining_no_rebates_qty);
				    } 
/*				    else {
					$qty_to_refund = min($qty_to_refund, $rebate->getMaxQty() - $qty_already_refunded);
					if ($qty_to_refund < 0) {
						$qty_to_refund = 0;
					}
				    }
*/
	
				    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$rebateAmount = ($rebate->getCap() / 100.0) * $qty_to_refund;	
				    }
				    else {
					$rebateAmount = $rebate->getAmount() * $qty_to_refund;	
				    }


				    if ($rebate->getMaxQty() < $orderqty) {
					$rebate_fullqty = $rebate->getMaxQty();
				    } else {
					$rebate_fullqty = $orderqty;
				    }
				    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$rebateAmount = $price * ($rebate->getCap() / 100.0) * $qty_to_refund;	
					$fullRebateAmount = $price * ($rebate->getCap() / 100.0) * $rebate_fullqty;	
				    }
				    else {
					$rebateAmount = $rebate->getAmount() * $qty_to_refund;	
					$fullRebateAmount = $rebate->getAmount() * $rebate_fullqty;	
				    }
				    $itemPriorDiscount = $orderitem->getDiscountAmount() - $fullRebateAmount;
				    $curCouponDiscount = $itemPriorDiscount * ($full_qty_to_refund / $orderqty);
				
				    $this->logger->info("in memo inv total, rebate amount " . $rebateAmount . ", current invoice coupon discount " . $curCouponDiscount . ", full rebate " . $fullRebateAmount . ", full rebateable qty " . $rebate_fullqty . ", qty to refund " . $qty_to_refund . ", already refunded " . $qty_already_refunded . ", order qty " . $orderqty . ", order item discount " . $orderitem->getDiscountAmount() . ", item prior discount " . $itemPriorDiscount);
				    $this->logger->info("setting discount amount " . ($curCouponDiscount + $rebateAmount));
				    $totalMemoDiscount += $curCouponDiscount + $rebateAmount;
	
				    $memoitem->setDiscountAmount($curCouponDiscount + $rebateAmount);
				    $memoitem->setBaseDiscountAmount($curCouponDiscount + $rebateAmount);
				    if ($parent) {
					$parent->setDiscountAmount($curCouponDiscount + $rebateAmount);
					$parent->setBaseDiscountAmount($curCouponDiscount + $rebateAmount);
				    }
				    //$memoitem->setRowTotalWithDiscount($qty * $price - $rebateAmount);
				}
			}
			if (!$gotrebate) {
				$child_has_rebate = 0; 
				if ($orderitem->getProductType() == "configurable") {
					foreach ($memo->getAllItems() as $possible_child) {
						if ($possible_child->getOrderItemId() == $orderitem->getItemId()) {
							$quoteitem = $this->quoteItemFactory->create()->load($possible_child->getQuoteItemId(), 'item_id');
							$rebate = $this->rebateFactory->create()->load($quoteitem->getId(), 'item_id'); 
							if ($rebate->getAmount()) {
								$child_has_rebate = true;
							}
							break;
						}
					}
				}
				if (!$child_has_rebate) {	
					$totalMemoDiscount += $memoitem->getDiscountAmount();
				}

			}
		}
		if ($hasRebate) { 
			 $memo->setDiscountDescription( $memo->getOrder()->getDiscountDescription());
			 $memo->setDiscountAmount(-$totalMemoDiscount);
			 //$memo->setGrandTotal($memo->getSubtotal() - $totalMemoDiscount);
			// $this->logger->info("grand total " . $memo->getGrandTotal() . ", discount " . $memo->getDiscountAmount() . ", subtotal " . $memo->getSubtotal());
		}
		return $this;
	 }
}
