<?php

class Unipayment_Payment_Model_Source_Processingstatus
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'Confirmed', 'label' => Mage::helper('unipayment')->__('Confirmed')),
            array('value' => 'Complete', 'label' => Mage::helper('unipayment')->__('Complete')),						
        );
    }
}



