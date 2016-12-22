<?php namespace Ashsmith\Blog\Model\ResourceModel\Post;
namespace Magento\Quote\Model\ResourceModel\Quote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\Collection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Quote\Model\Rebate', 'Magento\Quote\Model\ResourceModel\Rebate');
    }

}
