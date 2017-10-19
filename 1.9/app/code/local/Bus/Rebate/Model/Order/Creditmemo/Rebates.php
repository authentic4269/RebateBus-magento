<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Bus_Rebate_Model_Order_Creditmemo_Rebates extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    protected function getParentMemoItem($memo, $sku) {
	foreach($memo->getAllItems() as $memo_item) {
		if ($memo_item->getOrderItem()->getProductType() == 'configurable' && $memo_item->getSku() == $sku) {
			return $memo_item;	
		}
	}
	return NULL;
    }



    public function collect(Mage_Sales_Model_Order_Creditmemo $memo)
    {
        parent::collect($memo);
 
	$totalRebateAmount = 0;
	$baseTotalRebateAmount = 0;
	$subtotalWithDiscount = 0;
	$baseSubtotalWithDiscount = 0;
        if (!count($memo->getAllItems())) {
            return $this; //this makes only address type shipping to come through
        }
 
        foreach ($memo->getAllItems() as $memo_item) {
		$order_item = $memo_item->getOrderItem();	
		if ($order_item->getProductType() == 'simple' || $order_item->getProductType() == 'grouped') {
			$rebate= Mage::getModel('rebate/rebate')->load($order_item->getQuoteItemId(), 'item_id');
			if ($rebate->getId()) {
			    $rebateAmount = 0;
			    $memoqty = 0;
			    $program = $rebate->getProgram();
			    $memoqty = $memo_item->getQty();
			    $old_memoqty = $memo_item->getQtyInvoiced();;
			    if ($order_item->getParentItemId() && $order_item->getParentItem()->getProductType() == 'configurable') {
				$parentitem = $this->getParentMemoItem($memo, $memo_item->getSku());
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
			    $totalRebateAmount += $rebateAmount;
			} 
		}
	
	}

	$memo->setGrandTotal($memo->getGrandTotal() - $totalRebateAmount);
	$memo->setBaseGrandTotal($memo->getBaseGrandTotal() - $totalRebateAmount);

    }

}

?>
