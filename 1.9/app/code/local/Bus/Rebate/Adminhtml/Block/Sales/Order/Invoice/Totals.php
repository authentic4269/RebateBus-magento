<?php 

class Bus_Rebate_Adminhtml_Block_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
	$invoice = $this->_invoice;
        $amount = 0;
 	$items = $invoice->getAllItems();
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
			    $program = $rebate->getProgram();
			    if ($rebate->getMaxqty() < $item->getQtyOrdered()) {
				    $rebateAmount = $rebate->getAmount()*$rebate->getMaxqty();
			    }
			    else {
				    $rebateAmount = $rebate->getAmount() * $item->getQtyOrdered();
			    }
			    $amount += $rebateAmount;
			} 
		}
	}

        if ($amount) {
            $this->addTotalBefore(new Varien_Object(array(
                'code'      => 'bus_rebate',
                'value'     => $amount,
                'base_value'=> $amount,
                'label'     => 'Rebate Bus Incentive from ' . $program,
            ), array('shipping', 'tax')));
        }
 
        return $this;
    }
 
}
?>
