<?php
namespace Bus\Rebate\Model\Total\Quote;

class Rebate extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
	protected $_priceCurrency;
	protected $_logger;

	public function __construct(
		\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
		\Psr\Log\LoggerInterface $logger	
	) {
		$this->_priceCurrency = $priceCurrency;
		$this->_logger = $logger;
	}

	public function collect(
		\Magento\Quote\Model\Quote $quote,
		\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
		\Magento\Quote\Model\Quote\Address\Total $total
	) {

		parent::collect($quote, $shippingAssignment, $total);
		$this->_logger->addDebug('got here');
/*		$customDiscount = -10;
		$total->addTotalAmount('busrebate', $customDiscount);
		$total->addBaseTotalAmount('busrebate', $customDiscount);
		$total->setCustomDiscount($customDiscount);
*/
		return $this;
	}
}
?>
