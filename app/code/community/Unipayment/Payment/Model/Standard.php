<?php
require_once 'Api/vendor/autoload.php';

class Unipayment_Payment_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'unipayment_standard';
    protected $_formBlockType = 'unipayment/standard_form';
    protected $_infoBlockType = 'unipayment/standard_info';
	
    protected $_isGateway               = true;
    protected $_canAuthorize            = false;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_order = null;
	protected $uniPaymentClient;
		
	
    public function getConfig()
    {
        return Mage::getSingleton('unipayment/config');
    }


	public function getInfoInstance()
	{
		$payment = $this->getData('info_instance');
		if (! $payment)
		{
			$payment = $this->getOrder()->getPayment();
			$this->setInfoInstance($payment);
		}
		return $payment;
	}


	public function get_PaymentInfoData($key, $payment = null)
	{
		return $payment->getAdditionalInformation($key);
	}	
	
	
	public function get_TransactionId()
	{
		return $this->get_PaymentInfoData('transaction_id');
	}	
	

    
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();        		
		
        return $this;
    }
    
    public function getDescription ()
    {
        return $this->getConfig()->getDescription();
    }
	

    
    protected function getReturnUrl ()
    {
        return Mage::getUrl('unipayment/standard/returnresponse');
    }

    
    protected function getNotifyUrl ()
    {
        return Mage::getUrl('unipayment/standard/notifyresponse');
    }

    
    protected function getVendorTxCode ()
    {
        return $this->getOrder()->getRealOrderId();
    }

   
    protected function getFormattedCart ()
    {
        $items = $this->getOrder()->getAllItems();
        $resultParts = array();
        $totalLines = 0;
        if ($items) {
            foreach($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                $quantity = $item->getQtyOrdered();

                $cost = sprintf('%.2f', $item->getBasePrice() - $item->getBaseDiscountAmount());
                $tax = sprintf('%.2f', $item->getBaseTaxAmount());
                $costPlusTax = sprintf('%.2f', $cost + $tax/$quantity);

                $totalCostPlusTax = sprintf('%.2f', $quantity * $cost + $tax);

                $resultParts[] = str_replace(':', ' ', $item->getName());
                $resultParts[] = $quantity;
                $resultParts[] = $cost;
                $resultParts[] = $tax;
                $resultParts[] = $costPlusTax;
                $resultParts[] = $totalCostPlusTax;
                $totalLines++; 
            }
       }

       
       $shipping = $this->getOrder()->getBaseShippingAmount();
       if ((int)$shipping > 0) {
           $totalLines++;
           $resultParts = array_merge($resultParts, array('Shipping','','','','',sprintf('%.2f', $shipping)));
       }

       $result = $totalLines . ':' . implode(':', $resultParts);
       return $result;
    }


    
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('unipayment/form_standard', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());
        return $block;
    }

 
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('unipayment/standard/redirect');
    }    
	
	public function UniPaymentInit()
	{		
		$this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
		$this->uniPaymentClient->getConfig()->setDebug(false);
		$this->uniPaymentClient->getConfig()->setIsSandbox(($this ->getConfig()->getenvironment() == 'test') );						
		$this->uniPaymentClient->getConfig()->setClientId($this->getConfig()->getclientid());
		$this->uniPaymentClient->getConfig()->setClientSecret($this->getConfig()->getclientsecret());				
	}


    public function getStandardCheckoutFormFields ()
    {
        $order = $this->getOrder();
		$payment = $this->getInfoInstance();				

        $amount = $order->getBaseGrandTotal() ;
		$currencyCode = $order->getOrderCurrencyCode();				
		
		$order_id = $this->getVendorTxCode();		
		
		$desc = 'Order No : '.$order_id;
		$lang =  Mage::app()->getLocale()->getLocaleCode();
		$lang = str_replace('_', '-', $lang);
		
		
		$this->UniPaymentInit();
		$createInvoiceRequest = new \UniPayment\Client\Model\CreateInvoiceRequest();
		$createInvoiceRequest->setAppId($this->getConfig()->getappid());
		$createInvoiceRequest->setPriceAmount($amount);
		$createInvoiceRequest->setPriceCurrency($currencyCode);
		if ($this->getConfig()->getpaycurrency() != '-') {
			$createInvoiceRequest->setPayCurrency($this->getConfig()->getpaycurrency());
		}
		$createInvoiceRequest->setOrderId($order_id);
		$createInvoiceRequest->setConfirmSpeed($this->getConfig()->getconfirmspeed());
		$createInvoiceRequest->setRedirectUrl($this->getReturnUrl());
		$createInvoiceRequest->setNotifyUrl($this->getNotifyUrl());
		$createInvoiceRequest->setTitle($desc);
		$createInvoiceRequest->setDescription($desc);
		$createInvoiceRequest->setLang($lang);
		
		try {		
			
	  		$response = $this->uniPaymentClient->createInvoice($createInvoiceRequest);							  
			
	 	}
		catch(Exception $e) {
			echo $e->getMessage();
			exit;
		}		
			
		
					
		if ($response['code'] == 'OK'){
			$payurl = $response->getData()->getInvoiceUrl();					
		}
		else {
			$errmsg = $response['msg'];
			$session = Mage::getSingleton('checkout/session');
			$session->setErrorMessage($errmsg);	
			$this->_redirect('unipayment/standard/failure');				
			exit;
		}    					
		header("Location: $payurl");
		die();
    }
	
	public function UnipaymentResponse ()	{
		
		$notify_json = file_get_contents('php://input');		
		$notify_ar = json_decode($notify_json, true);
		$order_id =  $notify_ar['order_id'];
		
		
		$this->UniPaymentInit();	
		$response = $this->uniPaymentClient->checkIpn($notify_json);	
		
		$status = 'New';
		$invoice_id = '';
		if ($response['code'] == 'OK'){						
			$error_status = $notify_ar['error_status'];						
			$status = $notify_ar['status'];
			$invoice_id = $notify_ar['invoice_id'];				   
			
			$order = Mage::getModel('sales/order');
        	$order->loadByIncrementId($order_id);
			$order_note = '';			
			
						
			if($this->getConfig()->getprocessingstatus() == $status) {	
				
				$order->getPayment()->setTransactionId($invoice_id);
            	$order->getPayment()->setAdditionalInformation('transaction_id', $invoice_id);
				if ($this->saveInvoice($order)) {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                } 
			}
				
			
			 switch($status)
				{
					case 'New':
					{
						//$order -> add_order_note('Invoice : '.$invoice_id.' created');	
						break;
					}
					case 'Paid':
					{
						$order_note ='Invoice : '.$invoice_id.' transaction detected on blockchain';	
						break;
					}

					case 'Confirmed':
					{
						
						$order_note ='Invoice : '.$invoice_id.' has changed to confirmed';				
						break;
					}
					case 'Complete':
					{						
						$order_note ='Invoice : '.$invoice_id.' has changed to complete';						
						break;
					}
					case 'Expired':
					{
						$order_note ='Invoice : '.$invoice_id.' has changed to expired';					
						if ($this->getConfig()->gethandleexpiredstatus == 1) {
							  $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
						}
						
						break;
					}
					case 'Invalid':
					{
						$order_note ='Invoice : '.$invoice_id.' has changed to invalid because of network congestion, please check the dashboard';												
						break;
					}


					default:
            				break;
				}
		}
		
		$order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('unipayment')->__($order_note)
        );
		$order->save();
        $order->sendNewOrderEmail();		
		
		
		echo "SUCCESS";				
		exit;		
	}
	
	protected function saveInvoice (Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();

            Mage::getModel('core/resource_transaction')
               ->addObject($invoice)
               ->addObject($invoice->getOrder())
               ->save();
            return true;
        }

        return false;
    }
	
	
	public  function getCurrencies ($fiat = false)		
    {		
				
		$this->uniPaymentClient = new \UniPayment\Client\UniPaymentClient();
		$this->uniPaymentClient->getConfig()->setDebug(false);
		$this->uniPaymentClient->getConfig()->setIsSandbox(($this ->getConfig()->getenvironment() == 'test') );						
	  
	  $currencies = array();	  		  	
	 try {		
	  $apires = $this->uniPaymentClient->getCurrencies();
	  if ($apires['code'] == 'OK') {
		 foreach ($apires['data'] as $crow){
			if ($crow['is_fiat'] == $fiat) $currencies[$crow['code']] = $crow['code'];			 
		 }		
	  }
	 }
		catch(Exception $e) {
			$currencies = array('BTC'=>'BTC');
		}		
	  return $currencies;        
    }		
	
		
}