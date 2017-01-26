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


/**
 * Rebate Resource Model
 *
 * @author      Mitch Vogel, Rebate Bus (mitch@rebatebus.com)
 */
class Bus_Rebate_Model_Resource_Rebate extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Main table and field initialization
     *
     */
    protected function _construct()
    {
        $this->_init('rebate/rebate', 'id');
    }
}
