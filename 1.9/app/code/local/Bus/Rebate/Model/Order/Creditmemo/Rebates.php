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
    public function collect(Mage_Sales_Model_Order_Creditmemo $memo)
    {
        parent::collect($memo);
 
	$totalRebateAmount = 0;
	$baseTotalRebateAmount = 0;
	$subtotalWithDiscount = 0;
	$baseSubtotalWithDiscount = 0;
	$order = $memo->getOrder(); 
        $items = $order->getAllVisibleItems();
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
 
        foreach ($items as $item) {
	 	$rebate= Mage::getModel('rebate/rebate')->load($item->getQuoteItemId(), 'item_id');
		$rebateAmount = 0;
		if ($rebate->getId()) {
		    if ($rebate->getMaxqty() < $item->getQtyOrdered()) {
			    $rebateAmount = $rebate->getAmount()*$rebate->getMaxqty();
		    }
		    else {
			    $rebateAmount = $rebate->getAmount() * $item->getQtyOrdered();
		    }
		    $totalRebateAmount += $rebateAmount;
		    $baseTotalRebateAmount += $rebateAmount;

//		    $item->setRowTotalWithDiscount($item->getRowTotalWithDiscount() - $rebateAmount);
//		    $item->setBaseRowTotalWithDiscount($item->getBaseRowTotalWithDiscount() - $rebateAmount);

                    $subtotalWithDiscount+=$item->getRowTotalWithDiscount();
                    $baseSubtotalWithDiscount+=$item->getBaseRowTotalWithDiscount();
		} 
	}
/*
	// TODO add similar invoice rebate amounts 
	$address->setRebatesAmount($totalRebateAmount);
	$address->setBaseRebatesAmount($baseTotalRebateAmount);
*/
	$order->setDiscountInvoiced(
		$order->getDiscountInvoiced() + $totalRebateAmount
	);
	$order->setBaseDiscountInvoiced(
		$order->getBaseDiscountInvoiced() + $totalRebateAmount
	);

	$memo->setGrandTotal($order->getGrandTotal() - $totalRebateAmount);
	$memo->setBaseGrandTotal($order->getBaseGrandTotal() - $totalRebateAmount);

    }

}

?>
