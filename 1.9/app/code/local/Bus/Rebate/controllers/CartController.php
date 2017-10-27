<?php 
require_once 'Mage/Checkout/controllers/CartController.php';
class Bus_Rebate_CartController extends Mage_Checkout_CartController
{
    /**
     * Add rebate
     */
    public function rebatesPostAction()
    {
	if ($this->getRequest()->getParam("remove")) {
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$cartItems = $quote->getAllItems();
		foreach ($cartItems as $item) {
			if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
				$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
				if ($rebate) {
					$this->_getSession()->addSuccess(
						'Incentive Removed'
					);
					$rebate->delete();
				}
			}
		}
	        $this->_goBack();

	} else {
		$productId = (string) $this->getRequest()->getParam('product');
		$verification = (string) $this->getRequest()->getParam('verification');
		$maxqty = (int) $this->getRequest()->getParam('maxqty');
		$amount = (float) $this->getRequest()->getParam('amount');
		$program = (string) $this->getRequest()->getParam('program');
		$busid = (string) $this->getRequest()->getParam('busid');
		$cap = (float) $this->getRequest()->getParam('cap');
		// No reason continue with empty shopping cart
		if (!$this->_getCart()->getQuote()->getItemsCount()) {
	            $this->_goBack();
		    return;
		}
		foreach (Mage::getModel('checkout/cart')->getQuote()->getAllItems() as $item) {
			if (($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') && $item->getSku() == $productId) {
				$model = Mage::getModel('rebate/rebate');
//				if ($item->getParentItemId() && $item->getParentItem()->getProduct()->getStockItem()->getProductTypeId() == 'configurable') {
				if ($item->getParentItemId() && $item->getParentItem()->getProductType() == 'configurable') {
					if ($amount > $item->getParentItem()->getPrice() * ($cap / 100.0))
						$amount = $item->getParentItem()->getPrice() * ($cap / 100.0);
				} 
				else {
					
					if ($amount > $item->getPrice() * ($cap / 100.0))
						$amount = $item->getPrice() * ($cap / 100.0);
				}
				$model->setAmount($amount);
				$model->setVerification($verification);
				$model->setMaxqty($maxqty);
				$model->setProgram($program);
				$model->setItemId($item->getId());	
				$model->setBusid($busid);
				$model->setCap($cap);
				$model->save();
				$this->_getSession()->addSuccess(
					'Rebate was applied'
				);
			}
		}
		$this->_getSession()->addError('Rebate for product %s not found in cart', Mage::helper('core')->escapeHtml($productId));
		$this->_goBack();
	}
    }


}
?>
