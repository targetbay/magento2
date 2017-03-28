<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalSubscriberInterface;

/**
 * Defines the implementaiton class of the TotalSubscriber.
 */
class TotalSubscriber implements TotalSubscriberInterface
{

	/**
	 * Get the Total Subscriber
	 *
	 * @return totals
	 */
	public function totalsubscribercount() {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$subscriberFactory = $objectManager->create('\Magento\Newsletter\Model\SubscriberFactory');

		$collection = $subscriberFactory->create()->getCollection();

		$totals = array (
					'total_subscriber' =>  $collection->getSize()
		);
		
		return json_encode($totals);
	}
}
