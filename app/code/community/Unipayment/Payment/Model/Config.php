<?php

class Unipayment_Payment_Model_Config extends Varien_Object
{

    /**
     *  Return config var
     *
     *  @param    string Var key
     *  @param    string Default value for non-existing key
     *  @return	  mixed
     */
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
             $value = Mage::getStoreConfig('payment/unipayment_standard/'.$key);
             if (is_null($value) || false===$value) {
                 $value = $default;
             }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    public function getDescription ()
    {
        return $this->getConfigData('description');
    }

    


    public function getappid ()
    {
        return $this->getConfigData('app_id');
    }

	public function getclientid ()
    {
        return $this->getConfigData('client_id');
    }
	
    public function getclientsecret ()
    {
        return $this->getConfigData('client_secret');
    }

	public function getconfirmspeed ()
    {
        return $this->getConfigData('confirm_speed');
    }
	
	public function getpaycurrency ()
    {
        return $this->getConfigData('pay_currency');
    }
	
	public function getprocessingstatus ()
    {
        return $this->getConfigData('processing_status');
    }
	
	public function gethandleexpiredstatus ()
    {
        return $this->getConfigData('handle_expired_status');
    }
	
	public function getenvironment ()
    {
        return $this->getConfigData('environment');
    }	
	
	public function getpayurl ()
    {
		return ($this -> getenvironment() == 'test') ? 'https://unipayment.io' : 'https://sandbox.unipayment.io';
    }

}