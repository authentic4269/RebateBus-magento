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


class Bus_Rebate_Model_Order_Invoice_Rebates extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    protected function getParentInvoiceItem($invoice, $sku) {
	foreach($invoice->getAllItems() as $invoice_item) {
		if ($invoice_item->getOrderItem()->getProductType() == 'configurable' && $invoice_item->getSku() == $sku) {
			return $invoice_item;
		}
	}
	return NULL;
    }


    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        parent::collect($invoice);
 
	$totalRebateAmount = 0;
	$baseTotalRebateAmount = 0;
	$subtotalWithDiscount = 0;
	$baseSubtotalWithDiscount = 0;
//	$order = $invoice->getOrder(); 
 //       $items = $order->getAllItems();
	$items = $invoice->getAllItems();
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
        foreach ($items as $invoice_item) {
		$item = $invoice_item->getOrderItem();
		if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
	
			$rebate= Mage::getModel('rebate/rebate')->load($item->getQuoteItemId(), 'item_id');
			$rebateAmount = 0;
			if ($rebate->getId()) {
			    $invoicedqty = $invoice_item->getQty();
			    $old_invoicedqty = $invoice_item->getQtyInvoiced();
			    if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
			
				$parentitem = $this->getParentInvoiceItem($invoice, $item->getSku());
				if ($parentitem == NULL) {
					$invoicedqty = $parentitem->getQty();
					$old_invoicedqty = $parentitem->getQtyInvoiced();
				}	
				else {
					$invoicedqty = $parentitem->getQty();
					$old_invoicedqty = $parentitem->getQtyInvoiced();
				}	
			    }
		
			    $limqty = max($rebate->getMaxqty() - $old_invoicedqty, 0);
			    $rebateqty = min($limqty, $invoicedqty); 

//			    Mage::log("qty to invoice: " . $item->getQtyToInvoice(), null, "rebatebus.log");
//			    Mage::log("qty invoiced: " . $item->getQtyInvoiced(), null, "rebatebus.log");
//			    Mage::log("rebateqty: " . $item->getQtyInvoiced(), null, "rebatebus.log");
			    if ($rebateqty > 0)
				$rebateAmount = $rebate->getAmount() * $rebateqty;
	
			    $totalRebateAmount += $rebateAmount;
			    $baseTotalRebateAmount += $rebateAmount;

			    $invoice_item->setRowTotalWithDiscount($invoice_item->getRowTotalWithDiscount() - $rebateAmount);
			    $invoice_item->setBaseRowTotalWithDiscount($invoice_item->getBaseRowTotalWithDiscount() - $rebateAmount);

			    $subtotalWithDiscount+=$item->getRowTotalWithDiscount();
			    $baseSubtotalWithDiscount+=$item->getBaseRowTotalWithDiscount();
			} 
		} 
	}
/*
	// TODO add similar invoice rebate amounts 
	$address->setRebatesAmount($totalRebateAmount);
	$address->setBaseRebatesAmount($baseTotalRebateAmount);
*/
/*
	$order->setDiscountInvoiced(
		$order->getDiscountInvoiced() + $totalRebateAmount
	);
	$order->setBaseDiscountInvoiced(
		$order->getBaseDiscountInvoiced() + $totalRebateAmount
	);
*/

	$invoice->setGrandTotal($invoice->getGrandTotal() - $totalRebateAmount);
	$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $totalRebateAmount);
    }
}
