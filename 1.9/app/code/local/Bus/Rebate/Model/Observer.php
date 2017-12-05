<?php

class Bus_Rebate_Model_Observer {
	public function sendInvoiceEmail($observer) {
	        Mage::log("in send invoice email observer", null, "rebatebus.log");
		$template_id    =   'sales_email_invoice_template';
		  $emailTemplate  =   Mage::getModel('core/email_template')->loadDefault($template_id);
		  $storeId =   Mage::app()->getStore()->getStoreId();
		  $invoice =   $observer->getEvent()->getInvoice();  
		  $order   =   $observer->getEvent()->getInvoice()->getOrder();
	          Mage::log("invoice id: " . $invoice->getId(), null, "rebatebus.log");
	          Mage::log("order id: " . $order->getId(), null, "rebatebus.log");
		if ($order->hasInvoices()) 
                {
			foreach ($order->getInvoiceCollection() as $inv) 
			{
	          	    Mage::log("got inv : " . $inv->getId(), null, "rebatebus.log");
			    $hasRebates = false;
			    $program = "";
			    $code = "";
			    $first = 1;
			    foreach ($inv->getAllItems() as $item) {
	        		Mage::log("this item " . $item->getSku(), null, "rebatebus.log");
				$rebate= Mage::getModel('rebate/rebate')->load($item->getOrderItem()->getQuoteItemId(), 'item_id');
				if ($rebate->getId()) {
					$hasRebates = true;
					$program = $rebate->getProgram();
				    	if ($first) 
						$code = $code . ', '; 
					else
						$first = 0;
					$code = $code . $rebate->getVerification();
				}
			    } if ($hasRebates) {
	        		    Mage::log("got rebate", null, "rebatebus.log");
				    $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())->setIsSecureMode(true);
				    $paymentBlockHtml = $paymentBlock->toHtml();

				    $email_to = 'rebatebus.invoices@gmail.com';//dynamic email address
				    $email_template_variables = array(
				    	'order' => $order,
				    	'invoice' => $invoice,
				    	'payment_html' => $paymentBlockHtml
				    );

				    $sender_name = Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_STORE_STORE_NAME);
				    $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
				    $emailTemplate->setSenderName($sender_name);
				    $emailTemplate->setSenderEmail($sender_email);
				    $processedTemplate = $emailTemplate->getProcessedTemplate($email_template_variables);
				    $headers  = 'MIME-Version: 1.0' . "\r\n";
				    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				    $headers .= 'From: '.$sender_name."\r\n".
				      'Reply-To: '.$sender_email."\r\n" .
				      'X-Mailer: PHP/' . phpversion();
				    //Send the email!
	        		    Mage::log("sending email", null, "rebatebus.log");
				    //$emailTemplate->send($email_to, $program, $email_template_variables);
				    //$emailTemplate->send($email_to, 'hi', $email_template_variables);
				    mail($email_to, $program . " Invoice for " . $code, $processedTemplate, $headers);
				    mail($email_to, $program . " Invoice for " . $code, $processedTemplate);
				
	        		    Mage::log($processedTemplate, null, "rebatebus.log");
				}
			}
                }
	}
}


?>
