<?php 

class Bus_Rebate_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
	$order = $this->getSource();
        $amount = 0;
	$program = "Rebate Bus";
 	$items = $order->getAllItems();
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
 
        foreach ($items as $item) {
		Mage::log("sku: " . $item->getSku(), null, "rebatebus.log");
		if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getQuoteItemId(), 'item_id');
			if ($rebate->getId()) {
			    $qty = 0;	
			    $program = $rebate->getProgram();
			    if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
				$qty = $item->getParentItem()->getQtyOrdered();
			    } else {
				$qty = $item->getQtyOrdered();
			    }
			    Mage::log("got qty " . $qty . ", sku: " . $item->getSku(), null, "rebatebus.log");
			    $rebateAmount = 0;
			    if ($rebate->getMaxqty() < $qty) {
				    $rebateAmount = $rebate->getAmount()*$rebate->getMaxqty();
			    }
			    else {
				    $rebateAmount = $rebate->getAmount() * $qty;
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
                'label'     => 'Energy Efficiency Rebate from ' . $program,
            ), array('shipping', 'tax')));
        }
 
        return $this;
    }
 
}
?>
