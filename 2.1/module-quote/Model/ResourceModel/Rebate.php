<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Quote Resource Rebate
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rebate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements
{
    /**
     * Constructor adds unique fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rebates', 'code');
        $this->addUniqueField(['field' => 'code', 'title' => __('Rebate with the same code')]);
        $this->addUniqueField(['field' => 'quote_item', 'title' => __('Rebate already applied to same product')]);
    }
}
