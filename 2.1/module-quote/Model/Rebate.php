<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\RebateInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Cart Totals
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @codeCoverageIgnore
 */
/*
    const KEY_REBATE_CODE= 'code';
    const KEY_AMOUNT = 'amount';
    const KEY_QUOTE = 'quote';
    const KEY_QUOTE_ITEM = 'quote_item';
    const KEY_MAXQTY = 'maxqty';
*/


class Rebate extends AbstractExtensibleModel implements RebateInterface
{
    public function _construct() 
    {
        parent::_construct();
	$this->_init('Magento\Quote\Model\Rebate');
    }

    public function getCode() {
	return $this->getData(self::KEY_REBATE_CODE);
    }

    /**
     * Sets the item code in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setCode($code) {
        return $this->setData(self::KEY_REBATE_CODE, $code);
    }


    /**
     * Returns the item price in quote currency.
     *
     * @return float Rebate price in quote currency.
     */
    public function getMaxqty() {
	return $this->getData(self::KEY_MAXQTY);
    }

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setMaxqty($maxqty) {
        return $this->setData(self::KEY_REBATE_MAXQTY, $maxqty);
    }

    /**
     * Sets the quote_item that this offer is associated with
     *
     * @param float $price
     * @return $this
     */
    public function setQuoteItem($quote_item) {
        return $this->setData(self::KEY_QUOTE_ITEM, $maxqty);
    }


    /**
     * Gets the quote item
     *
     * @return float Rebate price in quote currency.
     */
    public function getQuoteItem() {
	return $this->getData(self::KEY_QUOTE_ITEM);
    }

    /**
     * Returns the rebae amount in quote currency.
     *
     * @return float Rebate price in quote currency.
     */
    public function getAmount() {
	return $this->getData(self::KEY_AMOUNT);
    }

    /**
     * Sets the rebate amount in quote currency.
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount) {
	return $this->setData(self::KEY_AMOUNT, $amount);
    }


    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\RebateExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\RebateExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
