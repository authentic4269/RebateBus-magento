<?php
namespace Bus\Rebate\Model\ResourceModel\Rebate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'bus_rebate_rebate_collection';
    protected $_eventObject = 'rebate_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bus\Rebate\Model\Rebate', 'Bus\Rebate\Model\ResourceModel\Rebate');
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'id', $labelField = 'verification', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
}

