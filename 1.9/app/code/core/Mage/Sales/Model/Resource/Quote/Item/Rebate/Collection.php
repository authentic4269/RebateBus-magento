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


/**
 * Item rebate collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Quote_Item_Rebate_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Array of rebate ids grouped by item id
     *
     * @var array
     */
    protected $_rebatesByItem        = array();

    /**
     * Define resource model for collection
     *
     */
    protected function _construct()
    {
        $this->_init('sales/quote_item_rebate');
    }

    /**
     * Fill array of rebates by item and product
     *
     * @return Mage_Sales_Model_Resource_Quote_Item_Rebate_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $rebate) {
            $rebateId   = $rebate->getId();
            $itemId     = $rebate->getItemId();
            if (isset($this->_rebatesByItem[$itemId])) {
                $this->_rebatesByItem[$itemId][] = $rebateId;
            } else {
                $this->_rebatesByItem[$itemId] = array($rebateId);
            }
        }

        return $this;
    }

    /**
     * Apply quote item(s) filter to collection
     *
     * @param int | array $item
     * @return Mage_Sales_Model_Resource_Quote_Item_Rebate_Collection
     */
    public function addItemFilter($item)
    {
        if (empty($item)) {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
            //$this->addFieldToFilter('item_id', '');
        } elseif (is_array($item)) {
            $this->addFieldToFilter('item_id', array('in' => $item));
        } elseif ($item instanceof Mage_Sales_Model_Quote_Item) {
            $this->addFieldToFilter('item_id', $item->getId());
        } else {
            $this->addFieldToFilter('item_id', $item);
        }

        return $this;
    }

    /**
     * Get all rebate for item
     *
     * @param mixed $item
     * @return array
     */
    public function getRebatesByItem($item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item) {
            $itemId = $item->getId();
        } else {
            $itemId = $item;
        }

        $this->load();

        $rebates = array();
        if (isset($this->_rebatesByItem[$itemId])) {
            foreach ($this->_rebatesByItem[$itemId] as $rebateId) {
                $rebates[] = $this->_items[$rebateId];
            }
        }

        return $rebates;
    }

}
