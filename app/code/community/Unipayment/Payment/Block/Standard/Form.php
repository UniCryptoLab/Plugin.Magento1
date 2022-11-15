<?php


class Unipayment_Payment_Block_Standard_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('unipayment/standard/form.phtml');		
    }
	
	public function getPaycurrency() {
        $currency = Mage::getModel('unipayment/config')->getpaycurrency();
        return $currency;
    }
	
	public function getCurrencies() {
        $currencies = Mage::getSingleton('unipayment/standard')->getCurrencies();
        return $currencies;
    }
    

}
