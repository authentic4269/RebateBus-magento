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
 * @package     Mage_Checkout
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart api
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Checkout_Model_Cart_Rebates_Api extends Mage_Checkout_Model_Api_Resource
{
    /**
     * @param  $quoteId
     * @param  $itemId
     * @param  $storeId
     * @return bool
     */
    public function add($quoteId, null)
    {
	  $newrebate = Mage::getModel("sales/quote_item_rebate");  
	  $newrebate->setItemId(5);
	  $newrebate->setItemid(5);
	  $newrebate->setRebatebusid(1);
	  $newrebate->setProduct(1);
	  $newrebate->setMaxqty(1);
	  $newrebate->setTotal(1);
	  $newrebate->save();
	  return true;
    //    return $this->_applyCoupon($quoteId, $couponCode, $store = null);
    }

    /**
     * @param  $quoteId
     * @param  $storeId
     * @return void
     */
    public function remove($quoteId, $store = null)
    {
      return;
      /*  $couponCode = '';
        return $this->_applyCoupon($quoteId, $couponCode, $store);
      */
    }

    /**
     * @param  $quoteId
     * @param  $storeId
     * @return string
     */
    public function get($quoteId, $store = null)
    {
	return "";
        /*$quote = $this->_getQuote($quoteId, $store);

        return $quote->getCouponCode();
	*/
    }

    /**
     * @param  $quoteId
     * @param  $store
     * @return bool
     */
    protected function _applyRebate($quoteId, $store = null)
    {
	return true;
/*        $quote = $this->_getQuote($quoteId, $store);

        if (!$quote->getItemsCount()) {
            $this->_fault('quote_is_empty');
        }

        $oldCouponCode = $quote->getCouponCode();
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            return false;
        }

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
        } catch (Exception $e) {
            $this->_fault("cannot_apply_coupon_code", $e->getMessage());
        }

        if ($couponCode) {
            if (!$couponCode == $quote->getCouponCode()) {
                $this->_fault('coupon_code_is_not_valid');
            }
        }

        return true;
*/
    }


}
