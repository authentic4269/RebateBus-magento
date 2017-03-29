<?php 

class Bus_Rebate_Adminhtml_Block_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
	$order = $this->getCreditmemo()->getOrder();
        $amount = 0;
 	$items = $order->getAllVisibleItems();
	$program = "";
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
 
        foreach ($items as $item) {
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
