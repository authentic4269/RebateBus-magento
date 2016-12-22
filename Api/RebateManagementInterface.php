<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Api;

/**
 * Rebate management service interface.
 * @api
 */
interface RebateManagementInterface
{
    /**
     * Returns information for a rebate in a specified cart on a specified product.
     *
     * @param int $cartId The cart ID.
     * @return string The coupon code data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);

    /**
     * Adds a rebate code to a specified cart. Note that the amount cannot be set from the client
     * due to additional verification step done with the Rebate Bus API before order is placed.
     *
     * @param int $cartId The cart ID.
     * @param int $quoteItem The quote_item id
     * @param int $maxqty Maximum # of items which qualify for the rebate
     * @param int $amount Amount per item
     * @param string $rebateCode the rebate code
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified coupon could not be added.
     */
    public function set($cartId, $rebateCode, $quoteItem, $maxqty, $amount);

    /**
     * Deletes a rebate from a specified product in the cart.
     *
     * @param int $cartId The cart ID.
     * @param int $rebateCode The rebate code
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotDeleteException The specified coupon could not be deleted.
     */
    public function remove($cartId, $rebateCode);
}
