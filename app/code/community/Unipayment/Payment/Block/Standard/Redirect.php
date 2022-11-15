<?php


class Unipayment_Payment_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $standard = Mage::getModel('unipayment/standard');
        $form = new Varien_Data_Form();
		$ResAr = $standard->setOrder($this->getOrder())->getStandardCheckoutFormFields();
        $form->setAction($ResAr['url'])
            ->setId('unipayment_standard_checkout')
            ->setName('unipayment_standard_checkout')
            ->setMethod('GET')
            ->setUseContainer(true);
        $html = '<html><body>
        ';
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("unipayment_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}