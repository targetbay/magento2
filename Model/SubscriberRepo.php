<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\SubscriberRepoInterface;

/**
 * Defines the implementaiton class of the SubscriberRepoInterface.
 */
class SubscriberRepo implements SubscriberRepoInterface
{
	/**
	 * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
	 */
	protected $_trackingHelper;

	/**
	 * @param \Targetbay\Tracking\Helper\Data $trackingHelper
	 */
	public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper
	)
	{
		$this->_trackingHelper  = $trackingHelper;
	}

	/**
	 * Get the list of subscribers
	 *
	 * @return SubscriberRepoInterface[]
	 */
	public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$subscriberCollection = $objectManager->create('\Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory');

		$collection = $subscriberCollection->create()->addFieldToSelect('*');

		$collection->setCurPage($searchCriteria->getCurrentPage());
		$collection->setPageSize($searchCriteria->getPageSize());

		$customer = $collection->load()->toArray();
		
		return $customer;
	}
}
