<?php
namespace Bus\Rebate\Controller\Index;

class Addrebate extends \Magento\Framework\App\Action\Action
{
  protected $_rebateFactory;
  protected $_session;
  protected $_logger;

  /*
	@param \Magento\Framework\App\Action\Context $context
	@param \Bus\Rebate\Model\RebateFactory $rebateFactory
  */

  public function __construct(
\Bus\Rebate\Model\RebateFactory $rebateFactory,
\Magento\Checkout\Model\Session $session,
\Psr\Log\LoggerInterface $logger,
\Magento\Framework\App\Action\Context $context
  )
  {
    $this->_rebateFactory = $rebateFactory;    
    $this->_session = $session;
    $this->_logger = $logger;
    return parent::__construct($context);
  }

  public function execute()
  {
    $items = $this->_session->getQuote()->getAllVisibleItems();
    if ($this->getRequest()->getParam("remove")) {
        foreach ($items as $item) {
  		$rebate = $this->_rebateFactory->create()->getRebateById($item->getId());
		if ($rebate) {
			$rebate->delete();
			$this->messageManager->addSuccess( __('Incentive Removed!') );
		}
        }
    } else {
    	$this->_logger->debug('start');
	$productId = (int) $this->getRequest()->getParam('product');
	$verification = (string) $this->getRequest()->getParam('verification');
	$maxqty = (int) $this->getRequest()->getParam('maxqty');
	$amount = (float) $this->getRequest()->getParam('amount');
	$program = (string) $this->getRequest()->getParam('program');
	$busid = (string) $this->getRequest()->getParam('busid');
	$cap = (float) $this->getRequest()->getParam('cap');
	foreach ($items as $item) {
    		$this->_logger->debug('id: ' . $item->getProductId());
		if ($item->getProductId() == $productId) {
			if ($cap) {
				if ($item->getPrice() * ($cap / 100.0) < $amount)
					$amount = $item->getPrice() * ($cap / 100.0);
			}
  			$rebate = $this->_rebateFactory->create();
			$rebate->setAmount($amount);	
			$rebate->setVerification($verification);	
			$rebate->setMaxQty($maxqty);	
			$rebate->setProgram($program);	
			$rebate->setItemId($item->getId());	
			$rebate->setBusid($busid);	
			$rebate->setCap($cap);	
			$rebate->save();
			$this->messageManager->addSuccess( __('Incentive Applied!') );
			echo 'hello';
		}
        }
    }


  /*  $rebate = $this->_rebateFactory->create();
    //$rebate->sayHi();
    $rebate->setData('item_id', 1);
    $rebate->setData('amount', 1);
    $rebate->setData('maxqty', 1);
    $rebate->setData('cap', 1);
    $rebate->setData('busid', "232");
    $rebate->setData('program', "hello");
    try {
    	$rebate->save();
   	 echo $rebate->getProgram();
    } catch (\Exception $e) {
	echo $e;
    }
    exit;*/
  }
}
