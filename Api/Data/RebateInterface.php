<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface TotalsRebateInterface
 * @api
 */
interface RebateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */

    /**
     * Rebate id.
     */
    const KEY_REBATE_CODE= 'code';

    /**
     * Amount
     */
    const KEY_AMOUNT = 'amount';

    /**
     * Amount
     */
    const KEY_QUOTE_ITEM = 'quote_item';

    /**
     * Max quantity.
     */
    const KEY_MAXQTY = 'maxqty';


    /**
     * Returns the item price in quote currency.
     *
     * @return float Rebate price in quote currency.
     */
    public function getCode();

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setCode($code);


    /**
     * Returns the item price in quote currency.
     *
     * @return float Rebate price in quote currency.
     */
    public function getMaxqty();

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setMaxqty($maxqty);

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setQuoteItem($quote_item);


    /**
     * Returns the item price in quote currency.
     *
     * @return float Rebate price in quote currency.
     */
    public function getQuoteItem();

    /**
     * Returns the item price in quote currency.
     *
     * @return float Rebate price in quote currency.
     */
    public function getAmount();

    /**
     * Sets the item price in quote currency.
     *
     * @param float $price
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\TotalsRebateExtensionInterface|null
     */
    public function getExtensionAttributes();


    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\TotalsRebateExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\TotalsRebateExtensionInterface $extensionAttributes);
}
