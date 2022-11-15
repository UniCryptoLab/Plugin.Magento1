<?php

class Unipayment_Payment_Model_Source_Environment
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'test', 'label' => Mage::helper('unipayment')->__('SandBox')),
            array('value' => 'live', 'label' => Mage::helper('unipayment')->__('Live')),			
        );
    }
}



