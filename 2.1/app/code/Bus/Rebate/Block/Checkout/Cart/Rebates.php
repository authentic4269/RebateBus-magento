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
    $this->items = $session->getQuote()->getAllVisibleItems();
    parent::__construct($context);
}

public function getCartItems() {
	$text = "<ul style='display: none'>";	
	foreach ($this->items as $item) {
		$text = $text . "<li class='cart-rebate-target' id='" . $item->getSku() . "'></li>";
	}
	$text = $text . "</ul>";
	return $text;
}

public function getRebateTexts() {
	$text = "<table style='border-bottom: 1px solid lightgray; margin-bottom: 1em;'>";
	$this->logger->info("In getRebateTexts"); 
	foreach ($this->items as $item) {
		$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); 
		if ($rebate->getAmount()) {
			$amount = min($rebate->getMaxQty(), $item->getQty()) * $rebate->getAmount();
			$text = $text . "<tr style='padding: 1em 0 1em 0'><td><strong>" . $item->getName() . ":</strong></td><td class='price a-right' style='padding-left: 2em;'>$" . number_format($amount, 2) . " Incentive</td></tr>";
		}
	}
	$text = $text . "</table>";
	return $text;
}



public function hasRebates() {
    foreach ($this->items as $item) {
	$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); //getRebateByItemId($item->getId());
	$this->logger->info("In hasRebates"); 
	if ($rebate->getAmount()) {
		return 1;
	}
    }
    return 0;

}


}
