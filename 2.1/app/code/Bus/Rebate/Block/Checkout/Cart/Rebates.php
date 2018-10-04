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
    $this->items = $session->getQuote()->getAllItems();
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
	foreach ($this->items as $item) {
		if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
			$rebate = $this->rebateFactory->create()->load($item->getId(), 'item_id'); 
			$amount = 0;
			$qty = 0;
			$price = 0;
			$qtystr = "";
			if ($rebate->getAmount()) {
					
				$qty = $item->getQty();
				if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
					$price = $item->getParentItem()->getPrice();
					$qty = $item->getParentItem()->getQty();
				} else {
					$price = $item->getPrice();
				}

				if ($qty >= $rebate->getMaxQty()) {
					$qtystr = "Limit of " . $rebate->getMaxQty() . " Items with Incentive per Customer Applied.";
				}
				if (($price * ($rebate->getCap() / 100.0)) < $rebate->getAmount()) {
					$this->logger->info("applying price cap " . $qty . ", " . $price);
					$amount = ($rebate->getCap() / 100.0) * $price;	
				} else {
					$amount = $rebate->getAmount();	
				}
				$text = $text . "<tr style='padding: 1em 0 1em 0'><td><strong>" . $item->getName() . ":</strong></td><td class='price a-right' style='padding-left: 2em;'>$" . number_format($rebate->getAmount(), 2) . " up to " . $rebate->getCap() . "% of Final Product Price " . $rebate->getInvoiceItemName() . ". " . $qtystr  . "</td></tr>";
			}
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
