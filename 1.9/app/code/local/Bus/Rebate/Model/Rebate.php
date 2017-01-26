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
 * Item rebate model
 *
 * @method Mage_Sales_Model_Resource_Quote_Item_Option _getResource()
 * @method Mage_Sales_Model_Resource_Quote_Item_Option getResource()
 * @method int getItemId()
 * @method Mage_Sales_Model_Quote_Item_Option setItemId(int $value)
 * @method int getProductId()
 * @method Mage_Sales_Model_Quote_Item_Option setProductId(int $value)
 * @method string getCode()
 * @method Mage_Sales_Model_Quote_Item_Option setCode(string $value)
 * @method string getValue()
 * @method Mage_Sales_Model_Quote_Item_Option setValue(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Bus_Rebate_Model_Rebate extends Mage_Core_Model_Abstract
{
    protected $_item;
    protected $_verification;
    protected $_rebatebusid;
    protected $_maxqty;
    protected $_amount;
    protected $_program;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('rebate/rebate');
    }

    /**
     * Checks that item rebate model has data changes
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    /**
     * Set quote item
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  Mage_Sales_Model_Quote_Item_Option
     */
    public function setItem($item)
    {
        $this->setItemId($item->getId());
        $this->_item = $item;
        return $this;
    }

    /**
     * Get option item
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Initialize item identifier before save data
     *
     * @return Mage_Sales_Model_Quote_Item_Option
     */
    protected function _beforeSave()
    {
        if ($this->getItem()) {
            $this->setItemId($this->getItem()->getId());
        }
        return parent::_beforeSave();
    }

    /**
     * Clone option object
     *
     * @return Mage_Sales_Model_Quote_Item_Rebate
     */
    public function __clone()
    {
        $this->setId(null);
        $this->_item    = null;
        return $this;
    }
}
