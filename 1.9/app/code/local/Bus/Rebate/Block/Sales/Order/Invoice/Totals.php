<?php 

/*class Bus_Rebate_Adminhtml_Block_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals*/
class Bus_Rebate_Block_Sales_Order_Invoice_Totals extends Mage_Sales_Block_Order_Invoice_Totals
{
    protected function getParentInvoiceItemQty($invoice, $sku) {
	foreach($invoice->getAllItems() as $invoice_item) {
		if ($invoice_item->getOrderItem()->getProductType() == 'configurable' && $invoice_item->getSku() == $sku) {
			return $invoice_item->getQty();	
		}
	}
	return 0;
    }

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
/*	$order = $this->getInvoice()->getAllItems();*/
        $amount = 0;
// 	$items = $this->_invoice->getAllItems();
	$items = $this->getInvoice()->getAllItems();
	$program = "";
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
 
        foreach ($items as $invoice_item) {
		$item = $invoice_item->getOrderItem();
		if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getQuoteItemId(), 'item_id');
			if ($rebate->getId()) {
			    $rebateAmount = 0;
			    $invoicedqty = $invoice_item->getQty();
			    if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
				$invoicedqty = $this->getParentInvoiceItemQty($invoice, $item->getSku());
			    }
			    Mage::log("in frontend total loop, item qty " . $invoicedqty, null, "rebatebus.log");
			    $limqty = max($rebate->getMaxqty() - $item->getQtyInvoiced(), 0);
			    $rebateqty = min($limqty, $invoicedqty);
			    if ($rebateqty > 0)
				$rebateAmount = $rebate->getAmount() * $rebateqty;
			    $program = $rebate->getProgram();
			    $amount += $rebateAmount;
			} 
		} 
	}

        if ($amount) {
/*            $this->addTotalBefore(new Varien_Object(array(
                'code'      => 'bus_rebate',
                'value'     => $amount,
                'base_value'=> $amount,
                'label'     => 'Rebate Bus Incentive from ' . $program,
            ), array('shipping', 'tax')));
*/ 
            $this->addTotal(new Varien_Object(array(
                'code'      => 'bus_rebate',
                'value'     => -$amount,
                'base_value'=> -$amount,
                'label'     => 'Rebate Bus Incentive from ' . $program,
            )));
        }
 
        return $this;
    }
 
}
?>
