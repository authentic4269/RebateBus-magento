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
	Mage::log("collecting", null, "rebatebus.log");
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
	 	$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
		if ($rebate->getId()) {
		    $rebateAmount = 0;
		    if ($rebate->getMaxqty() < $item->getQty()) {
			    $rebateAmount = $rebate->getAmount()*$rebate->getMaxqty();
		    }
		    else {
			    $rebateAmount = $rebate->getAmount() * $item->getQty();
		    }
		    $totalRebateAmount += $rebateAmount;
		    $baseTotalRebateAmount += $rebateAmount;

		    $item->setRowTotalWithDiscount($item->getRowTotalWithDiscount() - $rebateAmount);
		    $item->setBaseRowTotalWithDiscount($item->getBaseRowTotalWithDiscount() - $rebateAmount);
                    $subtotalWithDiscount+=$item->getRowTotalWithDiscount();
                    $baseSubtotalWithDiscount+=$item->getBaseRowTotalWithDiscount();
		} 
	}
	Mage::log("qty: " . $item->getQty(), null, 'rebatebus.log');
	$address->setRebatesAmount($totalRebateAmount);
	$address->setBaseRebatesAmount($baseTotalRebateAmount);
	$address->setGrandTotal($address->getGrandTotal() - $totalRebateAmount);
	$address->setBaseGrandTotal($address->getBaseGrandTotal() - $totalRebateAmount);
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
	Mage::log("fetching", null, "rebatebus.log");
	if ($address->getRebatesAmount() > 0) {
		$address->addTotal(array(
			'code'=>"rebates",
			'title'=>"Incentives",
			'value'=>$address->getRebatesAmount()
		));
	}
        return $this;
    }

}