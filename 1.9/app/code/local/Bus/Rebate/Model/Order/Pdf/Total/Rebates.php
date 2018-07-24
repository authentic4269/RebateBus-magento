<?php

class Bus_Rebate_Model_Order_Pdf_Total_Rebates extends Mage_Sales_Model_Order_Pdf_Total_Default {

    protected function getParentItem($items, $sku) {
	foreach($items as $item) {
		if ($item->getProductType() == 'configurable' && $item->getSku() == $sku) {
			return $item;
		}
	}
	return NULL;
    }


   public function getTotalsForDisplay() {

      $items = $this->getOrder()->getAllItems();
 
	$totalRebateAmount = 0;
	$baseTotalRebateAmount = 0;
	$subtotalWithDiscount = 0;
	$baseSubtotalWithDiscount = 0;
//	$order = $invoice->getOrder(); 
 //       $items = $order->getAllItems();
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
	$invoiceitemname = "";
        foreach ($items as $item) {
		if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
	
			$rebate= Mage::getModel('rebate/rebate')->load($item->getQuoteItemId(), 'item_id');
			$rebateAmount = 0;
			if ($rebate->getId()) {
			    $invoiceitemname = $rebate->getInvoiceItemName();
			    $qty = $item->getQtyInvoiced();
			    $old_invoicedqty = 0;
			    $invoicedqty = $qty;
			    if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
				$parentitem = $this->getParentItem($items, $item->getSku());
				if ($parentitem !== NULL) {
					$invoicedqty = $parentitem->getQtyInvoiced();
				}	
			    }
		
			    $limqty = max($rebate->getMaxqty() - $old_invoicedqty, 0);
			    $rebateqty = min($limqty, $invoicedqty); 

			    if ($rebateqty > 0)
				$rebateAmount = $rebate->getAmount() * $rebateqty;
	
			    $totalRebateAmount += $rebateAmount;
			} 
		} 
	}



      $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
      $totals = array(array(
		"label" => $invoiceitemname,
		'amount' => "- $" . number_format($totalRebateAmount, 2),
		'font_size' => $fontSize
      ));

      return $totals;
   }

}


?>
