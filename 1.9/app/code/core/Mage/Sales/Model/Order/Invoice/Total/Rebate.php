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


class Mage_Sales_Model_Order_Invoice_Total_Rebate extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {

        $totalDiscountAmount     = $invoice->getDiscountAmount();
        $baseTotalDiscountAmount = $invoice->getBaseDiscountAmount();
/*
        if ($addShippingDicount) {
            $totalDiscountAmount     = $totalDiscountAmount + $invoice->getOrder()->getShippingDiscountAmount();
            $baseTotalDiscountAmount = $baseTotalDiscountAmount + $invoice->getOrder()->getBaseShippingDiscountAmount();
        }
*/
        /** @var $item Mage_Sales_Model_Order_Invoice_Item */
        foreach ($invoice->getAllItems() as $item) {
	   
            $orderItemDiscount      = (float) $orderItem->getDiscountAmount();
            $baseOrderItemDiscount  = (float) $orderItem->getBaseDiscountAmount();
            $orderItemQty       = $orderItem->getQtyOrdered();
	    $rebateCollection = Mage::getModel('sales/quote_item_rebate')->getCollection;


            if ($orderItemDiscount && $orderItemQty) {

                /**
                 * Resolve rounding problems
                 *
                 * We dont want to include the weee discount amount as the right amount
                 * is added when calculating the taxes.
                 *
                 * Also the subtotal is without weee
                 */
		
                $discount = $orderItemDiscount - $orderItem->getDiscountInvoiced();
                $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountInvoiced();

                if (!$item->isLast()) {
                    $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                    $discount = $invoice->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                    $baseDiscount = $invoice->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
                }

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($baseDiscount);

                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }

        $invoice->setDiscountAmount(-$totalDiscountAmount);
        $invoice->setBaseDiscountAmount(-$baseTotalDiscountAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
        return $this;
    }
}
