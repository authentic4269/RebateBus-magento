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
	$program = "";
        foreach ($items as $item) {
		Mage::log("item: " . $item->getSku(), null, "rebatebus.log");
		Mage::log("product type: " . $item->getProductType(), null, "rebatebus.log");
		if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
	
			$rebate= Mage::getModel('rebate/rebate')->load($item->getQuoteItemId(), 'item_id');
			$rebateAmount = 0;
			if ($rebate->getId()) {
			    $program = $rebate->getProgram();
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
			    Mage::log("invoiced qty: " . $invoicedqty, null, "rebatebus.log");
			    Mage::log("qty: " . $qty, null, "rebatebus.log");

			    if ($rebateqty > 0)
				$rebateAmount = $rebate->getAmount() * $rebateqty;
	
			    $totalRebateAmount += $rebateAmount;
			} 
		} 
	}



      $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
      $totals = array(array(
		"label" => 'Energy Efficiency Rebates from: ' . $program,
		'amount' => "- $" . number_format($totalRebateAmount, 2),
		'font_size' => $fontSize
      ));

      return $totals;
   }

}


?>
