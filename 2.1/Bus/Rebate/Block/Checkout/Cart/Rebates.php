<?php

namespace Bus\Rebate\Block\Checkout\Cart;

use Magento\Checkout\Model\Session;

class Rebates extends \Magento\Framework\View\Element\Template
{

protected $rebateFactory;
protected $session;
protected $logger;

public function __construct(
    \Bus\Rebate\Model\RebateFactory $rebateFactory,
    \Magento\Checkout\Model\Session $session,
    \Magento\Framework\View\Element\Template\Context $context
) {
    $this->session = $session;
    $this->rebateFactory = $rebateFactory;
    $this->logger = $context->getLogger();
    $items = $session->getQuote()->getAllVisibleItems();
    foreach($items as $item) {
    	$this->logger->debug($item->getId());
    }
   

    parent::__construct($context);
}

public function getRebateTexts() {
/*
	$text = "<table style='border-bottom: 1px solid lightgray; margin-bottom: 1em;'>";
	foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
		$rebate = $this->_rebateFactory->create()->loadByAttribute('item_id', $item->getId());
		if ($rebate) {
			$amount = min($rebate->getMaxqty(), $item->getQty()) * $rebate->getAmount();
			$text = $text . "<tr style='padding: 1em 0 1em 0'><td><strong>" . $item->getName() . ":</strong></td><td class='price a-right' style='padding-left: 2em;'>$" . number_format($amount, 2) . " Incentive</td></tr>";
		}
	}
	$text = $text . "</table>";
	return $text;
*/
	return 'foobar';
}



public function hasRebates() {
    $items = $this->session->getQuote()->getAllVisibleItems();
   
    foreach ($items as $item) {
	//$rebate = $this->rebateFactory->create()->loadByAttribute('item_id', $item->getId());
	$rebate = $this->rebateFactory->create()->getRebateById($item->getId());
	if ($rebate) {
		return 1;
	}
    }
    return 0;

}


}
