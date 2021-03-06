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


class Bus_Rebate_Model_Quote_Total_Rebates extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
 
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
	$totalRebateAmount = 0;
	$baseTotalRebateAmount = 0;
	$subtotalWithDiscount = 0;
	$baseSubtotalWithDiscount = 0;
 
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
 
        foreach ($items as $item) {
		if ($item->getProductType() == "simple" || $item->getProductType() == 'grouped') {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
			if ($rebate->getId()) {
			    $rebateAmount = 0;
			    $qty = 0;
			    $price = 0;
				
			    if ($item->getParentItemId() && $item->getParentItem()->getProduct()->getStockItem()->getProductTypeId() == 'configurable') {
				$qty = $item->getParentItem()->getQty();
				$price = $item->getParentItem()->getPrice();
			    } else {
				$qty = $item->getQty();
				$price = $item->getPrice();
			    }

			    if ($rebate->getMaxqty() < $qty) {
				    $qty = $rebate->getMaxqty();
			    }
				
			    if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
			        $rebateAmount = ($rebate->getCap() / 100.0) * $qty;	
			    }
			    else {
			        $rebateAmount = $rebate->getAmount() * $qty;	
			    }
			   

			    $totalRebateAmount += $rebateAmount;
			    $baseTotalRebateAmount += $rebateAmount;

			    $item->setRowTotalWithDiscount($item->getRowTotalWithDiscount() - $rebateAmount);
			    $item->setBaseRowTotalWithDiscount($item->getBaseRowTotalWithDiscount() - $rebateAmount);
			    $subtotalWithDiscount+=$item->getRowTotalWithDiscount();
			    $baseSubtotalWithDiscount+=$item->getBaseRowTotalWithDiscount();
			} 
		}
	}
	$address->setRebatesAmount($totalRebateAmount);
	$address->setBaseRebatesAmount($baseTotalRebateAmount);
	$address->setGrandTotal($address->getGrandTotal() - $totalRebateAmount);
	$address->setBaseGrandTotal($address->getBaseGrandTotal() - $totalRebateAmount);
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
	if ($address->getRebatesAmount() > 0) {
		$invoiceitemname = "";
        	$items = $this->_getAddressItems($address);
        	foreach ($items as $item) {
			$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
			if ($rebate->getId()) {
				$invoiceitemname = $rebate->getInvoiceItemName();
				break;
			}
		}
		$address->addTotal(array(
			'code'=>"rebates",
			'title'=>$invoiceitemname,
			'value'=>($address->getRebatesAmount() * -1.0)
		));
	}
        return $this;
    }

}
