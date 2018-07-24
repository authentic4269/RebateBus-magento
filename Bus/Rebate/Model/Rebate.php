<?php 
namespace Bus\Rebate\Model;
class Rebate extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface,
\Bus\Rebate\Model\Api\Data\RebateInterface
{
    const CACHE_TAG = 'bus_rebate_rebate';

    protected $_cacheTag = 'bus_rebate_rebate';

    protected $_eventPrefix = 'bus_rebate_rebate';

    protected function _construct()
    {
        $this->_init('Bus\Rebate\Model\ResourceModel\Rebate');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
