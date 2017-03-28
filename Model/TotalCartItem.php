<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalCartItemInterface;

/**
 * Defines the implementaiton class of the TotalCartItem.
 */
class TotalCartItem implements TotalCartItemInterface
{

	/**
	 * Get the Total Cartitem
	 *
	 * @return totals
	 */
	public function totalcartitemcount() {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$connection = $objectManager->create('\Magento\Framework\App\ResourceConnection');

		$quoteTable = $connection->getTableName('quote_item');

		$quoteCollection = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Collection')
					->addFieldToSelect(array(
								'customer_id',
								'customer_firstname', 
								'customer_lastname', 
								'customer_email', 
								'updated_at'))
					->addFieldToFilter('customer_email', array('neq' => ''))
					->addFieldToFilter('customer_id', array('neq' => ''));
;
		$quoteCollection->getSelect()->join(array('Q2'=> $quoteTable), '`main_table`.`entity_id` = `Q2`.`quote_id`', array('*'))->group('Q2.quote_id');

		$totals = array (
					'total_cartitem' =>  $quoteCollection->getSize()
		);
		
		return json_encode($totals);
	}
}
