<?php



$installer = $this;
/* @var $installer Unipayment_Payment_Model_Mysql4_Setup */

$installer->startSetup();

$installer->run("
CREATE TABLE `unipayment_order` (
   			  `unipayment_order_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `order_id` bigint(20) NOT NULL,
			  `referenceNumber` VARCHAR(255),
			  `paymentStatus` CHAR(1),			  
			  `currencyCode` VARCHAR(3),			  			  
			  `gatewayResponse` TEXT,			  			  			  
			  `paymentAmount` INT(11),			  			  
			  PRIMARY KEY (`unipayment_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
