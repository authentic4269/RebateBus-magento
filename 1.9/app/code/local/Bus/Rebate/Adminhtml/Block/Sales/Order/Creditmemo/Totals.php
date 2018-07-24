<?php 

class Bus_Rebate_Adminhtml_Block_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
{
    protected function getParentMemoItem($memo, $sku) {
	foreach($memo->getAllItems() as $memo_item) {
		if ($memo_item->getOrderItem()->getProductType() == 'configurable' && $memo_item->getSku() == $sku) {
			Mage::log("found parent qty: " . $memo_item->getSku() . ", qty: " . $memo_item->getQty(), null, "rebatebus.log");
			return $memo_item;	
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
	$items = $this->getCreditmemo()->getAllItems();
        $amount = 0;
	$program = "";
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
        foreach ($items as $memo_item) {
		$order_item = $memo_item->getOrderItem();	
		if ($order_item->getProductType() == 'simple' || $order_item->getProductType() == 'grouped') {
			$rebate= Mage::getModel('rebate/rebate')->load($order_item->getQuoteItemId(), 'item_id');
			if ($rebate->getId()) {
			    $rebateAmount = 0;
			    $memoqty = 0;
			    $program = $rebate->getProgram();
			    $memoqty = $memo_item->getQty();
			    $old_memoqty = $memo_item->getQtyInvoiced();
			    if ($order_item->getParentItemId() && $order_item->getParentItem()->getProductType() == 'configurable') {
				$parentitem = $this->getParentMemoItem($this->getCreditmemo(), $memo_item->getSku());
				if ($parentitem == NULL) {
					// This should never happen
					$memoqty = 0;
					$old_memoqty = 0;
				} else {
					$memoqty = $parentitem->getQty();
					$old_memoqty = $parentitem->getQtyInvoiced();
				}
			    }
			    $limqty = max($rebate->getMaxqty() - $old_memoqty, 0);
			    $rebateqty = min($limqty, $memoqty);
			    
			    $rebateAmount = $rebate->getAmount()*$rebateqty;
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
