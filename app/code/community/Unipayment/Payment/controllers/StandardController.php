<?php

class Unipayment_Payment_StandardController extends Mage_Core_Controller_Front_Action
{
    public $isValidResponse = false;	
    
    public function getStandard()
    {
        return Mage::getSingleton('unipayment/standard');
    }

    
    public function getConfig()
    {
        return $this->getStandard()->getConfig();
    }

    public function getDebug ()
    {
        return $this->getStandard()->getDebug();
    }

    
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setUnipaymentStandardQuoteId($session->getQuoteId());

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('unipayment')->__('Customer was redirected to UNIPAYMENT')
        );
        $order->save();

        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('unipayment/standard_redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }
	    	
	public function notifyResponseAction()
	{		
		$notifyres = $this->getStandard()->UnipaymentResponse();
	}
		
  	
	
    public function returnResponseAction()
    {
		
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getUnipaymentStandardQuoteId(true));
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
		Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();		
		
		if ($order->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING) {			
            $success_msg =  '<b>Payment Status : <b> Success';
            $session->setErrorMessage($success_msg);		 
			
			$this->_redirect('unipayment/standard/success');	
		}
		else {
			$success_msg =  '<b>Payment Status : <b> Fail';
            $session->setErrorMessage($success_msg);		 
			$this->_redirect('unipayment/standard/failure');				
		}       

    }

         

   
    public function failureAction ()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setUnipaymentStandardQuoteId($session->getQuoteId());
		
        if (!$session->getErrorMessage()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('unipayment/session');
        $this->renderLayout();
    }
	

     public function successAction ()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setUnipaymentStandardQuoteId($session->getQuoteId());
		
        if (!$session->getErrorMessage()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('unipayment/session');
        $this->renderLayout();

    }		
	
 			
}