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
		$cartItems = $quote->getAllVisibleItems();
		foreach ($cartItems as $item) {
		 	$rebate= Mage::getModel('rebate/rebate')->load($item->getId(), 'item_id');
			Mage::log("id " . $item->getId(), null, "rebatebus.log");
			if ($rebate) {
				Mage::log("found rebate", null, "rebatebus.log");
				$this->_getSession()->addSuccess(
					'Incentive Removed'
				);
				$rebate->delete();
			}
		}
	        $this->_goBack();

	} else {
		$productId = (int) $this->getRequest()->getParam('product');
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
			if ($item->getSku() == 	$productId) {
				$model = Mage::getModel('rebate/rebate');
				if ($cap) {
					if ($item->getPrice() * ($cap / 100.0) < $amount)
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
				Mage::log('saved rebate with amount ' . $model->getAmount(), null, 'rebatebus.log');
				$this->_getSession()->addSuccess(
					'Rebate was applied'
				);
				$this->_goBack();
			}
		}
		$this->_getSession()->addError('Rebate for product %s not found in cart', Mage::helper('core')->escapeHtml($productId));
		$this->_goBack();
	}
    }


}
?>
