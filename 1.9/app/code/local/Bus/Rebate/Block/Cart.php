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
 * @category    Rebate
 * @package     Rebatebus_Rebate
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Bus_Rebate_Block_Cart extends Mage_Core_Block_Template
{

    public function hasRebates() {
	foreach (Mage::getModel('checkout/cart')->getQuote()->getAllItems() as $item) {
		if ($item->getProductType() == 'simple') {
		 	$rebate = Mage::getModel('rebate/rebate')->load($item->getId(),'item_id');
			if ($rebate->getId()) {
				return true;
			}
		}
	}
	return false;
    }		
	
    public function getRebateImage() {
	foreach (Mage::getModel('checkout/cart')->getQuote()->getAllItems() as $item) {
		if ($item->getProductType() == 'simple') {
		 	$rebate = Mage::getModel('rebate/rebate')->load($item->getId(),'item_id');
			if ($rebate) {
				return "https://www.rebatebus.com/assets/programs/" . $rebate->getProgram() . ".png";
			}
		}
	}
	// shouldn't happen if we're showing applied incentives section
	return "";
    }

    public function getRebateTexts() {
	$text = "<table style='border-bottom: 1px solid lightgray; margin-bottom: 1em;'>";
	foreach (Mage::getModel('checkout/cart')->getQuote()->getAllItems() as $item) {
		if ($item->getProductType() == 'simple') {
			$rebate = Mage::getModel('rebate/rebate')->load($item->getId(),'item_id');
			if ($rebate->getId()) {
				$amount = min($rebate->getMaxqty(), $item->getQty()) * $rebate->getAmount();
				$text = $text . "<tr style='padding: 1em 0 1em 0'><td><strong>" . $item->getName() . ":</strong></td><td class='price a-right' style='padding-left: 2em;'>$" . number_format($amount, 2) . " Incentive</td></tr>";
			}
		}
	}
	$text = $text . "</table>";
	return $text;
    }
}
?>
