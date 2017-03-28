<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalOrderInterface;

/**
 * Defines the implementaiton class of the TotalOrder.
 */
class TotalOrder implements TotalOrderInterface
{

	/**
	 * Get the total count of order
	 *
	 * @return totals
	 */
	public function totalordercount() {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$orderFactory = $objectManager->create('Magento\Sales\Model\OrderFactory');

		$collection = $orderFactory->create()->getCollection();

		$totals = array (
					'total_orders' =>  $collection->getSize()
		);
		
		return json_encode($totals);
	}
}
