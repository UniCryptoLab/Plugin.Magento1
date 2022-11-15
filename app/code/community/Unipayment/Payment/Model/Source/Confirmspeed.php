<?php

class Unipayment_Payment_Model_Source_Confirmspeed
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'low', 'label' => Mage::helper('unipayment')->__('low')),
            array('value' => 'medium', 'label' => Mage::helper('unipayment')->__('medium')),			
			array('value' => 'high', 'label' => Mage::helper('unipayment')->__('high')),				
        );
    }
}



