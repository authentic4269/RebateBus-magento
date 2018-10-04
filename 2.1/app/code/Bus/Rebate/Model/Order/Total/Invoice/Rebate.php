<?php
namespace Bus\Rebate\Model\Order\Total\Invoice;

class Rebate extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
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
	 \Magento\Sales\Model\Order\Invoice $invoice
	 )
	 {
		$invoiceitemname = '';
		$totalInvoiceDiscount = 0;
		$initialDiscount = 0;
		$result = null;
		$initialDiscountDescription = "";
		$gotrebate = 0;
		foreach ($invoice->getAllItems() as $invoiceitem) {
			$orderitem = $invoiceitem->getOrderItem();
			$item = $this->quoteItemFactory->create()->load($orderitem->getQuoteItemId(), 'item_id');
			$gotrebate = 0;
			if ($orderitem->getProductType() == 'simple' || $orderitem->getProductType() == 'grouped' || $orderitem->getProductType() == 'virtual') {
				$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
				if ($rebate->getAmount()) {
				    $gotrebate = 1;
				    $invoiceitemname = $rebate->getInvoiceItemName();
				    $rebateAmount = 0;
				    $fullRebateAmount = 0;
				    $price = $orderitem->getPrice() * (1.0 - ($orderitem->getDiscountPercent() / 100.0));
				    $qty_to_invoice = $invoiceitem->getQty();
				    $orderqty = $orderitem->getQtyOrdered();
				    $qty_already_invoiced = $orderitem->getQtyInvoiced();
				    $parentitem = null;
				    $itemPriorDiscount = 0;
				    if ($orderitem->getParentItemId() && $orderitem->getParentItem()->getProductType() == "configurable") {
					$price = $orderitem->getParentItem()->getPrice() * (1.0 - ($orderitem->getParentItem()->getDiscountPercent() / 100.0));
					$orderqty = $orderitem->getParentItem()->getQtyOrdered();
				    	$qty_already_invoiced = $orderitem->getParentItem()->getQtyInvoiced();
					$parent_order_item_id = $orderitem->getParentItemId();
					foreach ($invoice->getAllItems() as $possible_parent) {
						if ($possible_parent->getOrderItemId() == $parent_order_item_id) {
							$qty_to_invoice = $possible_parent->getQty();
							if ($qty_to_invoice != 0) {
								$parentitem = $possible_parent;
								$itemPriorDiscount = $parentitem->getDiscountAmount() / $qty_to_invoice;
							}
							break;
						}
					}
				    } else {
					if ($qty_to_invoice != 0) {
						$itemPriorDiscount = $invoiceitem->getDiscountAmount() / $qty_to_invoice;
					}
				    }
				    $rebate_fullqty = $orderqty;

				    $remaining_no_rebates_qty = $orderqty - $rebate->getMaxQty() - $qty_already_invoiced;
				    $this->logger->info("remaining no-rebates qty " . $remaining_no_rebates_qty);
				    if ($remaining_no_rebates_qty > 0) {
					$qty_to_rebate = max(0, $qty_to_invoice - $remaining_no_rebates_qty);
				    } else { 
					$qty_to_rebate = $qty_to_invoice;
				    }
				    if ($rebate->getMaxQty() < $orderqty) {
					$rebate_fullqty = $rebate->getMaxQty();
				    } else {
					$rebate_fullqty = $orderqty;
				    }
				    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$rebateAmount = $price * ($rebate->getCap() / 100.0) * $qty_to_rebate;	
					$fullRebateAmount = $price * ($rebate->getCap() / 100.0) * $rebate_fullqty;	
				    }
				    else {
					$rebateAmount = $rebate->getAmount() * $qty_to_rebate;	
					$fullRebateAmount = $rebate->getAmount() * $rebate_fullqty;	
				    }
				    $itemPriorDiscount = $orderitem->getDiscountAmount() - $fullRebateAmount;
				    $curCouponDiscount = $itemPriorDiscount * ($qty_to_invoice / $orderqty);
				    $this->logger->info("in order inv total, rebate amount " . $rebateAmount . ", current invoice coupon discount " . $curCouponDiscount . ", full rebate " . $fullRebateAmount . ", full rebateable qty " . $rebate_fullqty . ", qty to rebate " . $qty_to_rebate );
				    $invoiceitem->setDiscountAmount($curCouponDiscount + $rebateAmount);
				    $invoiceitem->setBaseDiscountAmount($curCouponDiscount + $rebateAmount);
				    if ($parentitem) {
					    $parentitem->setDiscountAmount($curCouponDiscount + $rebateAmount);
					    $parentitem->setBaseDiscountAmount($curCouponDiscount + $rebateAmount);
				    }
				    $totalInvoiceDiscount += $curCouponDiscount + $rebateAmount;
				    $this->logger->info("adding to totalinvoicediscount " . ($curCouponDiscount + $rebateAmount) . ", " . $invoiceitem->getSku());
				} 
			}
			if (!$gotrebate) {
				$child_has_rebate = 0; 
				if ($orderitem->getProductType() == "configurable") {
					foreach ($invoice->getAllItems() as $possible_child) {
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
					$totalInvoiceDiscount += $invoiceitem->getDiscountAmount();
				}
			}
		}
		 if ($totalInvoiceDiscount) {
			 $invoice->setDiscountDescription( $invoice->getOrder()->getDiscountDescription());
			 $invoice->setDiscountAmount($totalInvoiceDiscount);
			//$invoice->setGrandTotal($invoice->getSubtotal() - $totalInvoiceDiscount);
			//$invoice->setBaseGrandTotal($invoice->getBaseSubtotal() - $totalInvoiceDiscount);
		 }
		 return $this;
	 }
}
