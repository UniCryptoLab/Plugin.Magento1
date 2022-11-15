<?php

class Unipayment_Payment_Model_Source_Paycurrency
{
    public function toOptionArray()
    {
		$currencies = $this->getStandard()->getCurrencies();
        $rescurrencies =  array(
            array('value' => '-', 'label' => Mage::helper('unipayment')->__('---')),    
        );
		foreach($currencies as $key => $vale)
		{
			$rescurrencies[] = array('value' => $key, 'label' => Mage::helper('unipayment')->__($vale));
		}
		return $rescurrencies;
    }
	
	public function getStandard()
    {
        return Mage::getSingleton('unipayment/standard');
    }
}



