<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\UpdateCouponInterface;

/**
 * Defines the implementaiton class of the UpdateCouponInterface.
 */
class UpdateCouponRepo implements UpdateCouponInterface
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
     	 * update coupon code.
	 * @throws \Exception
	 * @return int
     	 */
	public function updatecoupon() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryCollection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
		$categoryFactory = $objectManager->create('\Magento\Catalog\Model\CategoryFactory');

		$collection = $categoryCollection->create()->addAttributeToSelect('*');
		
		$collection->setCurPage($searchCriteria->getCurrentPage());
		$collection->setPageSize($searchCriteria->getPageSize());

		$category = $collection->load()->toArray();
		
		return $category;
	}
}
