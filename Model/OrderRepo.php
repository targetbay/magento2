<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\OrderRepoInterface;

/**
 * Defines the implementaiton class of the OrderRepoInterface.
 */
class OrderRepo implements OrderRepoInterface
{	
	CONST BILLING = 'billing';
	CONST SHIPPING = 'shipping';

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
	 * Get the list of orders
	 *
	 * @return OrderInterface[]
	 */
	public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$orderCollection = $objectManager->create('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
		$orderFactory = $objectManager->create('\Magento\Sales\Model\OrderFactory');
		$address = $objectManager->create('\Magento\Customer\Model\Address');

		$collection = $orderCollection->create()->addFieldToSelect('*');
		
		$collection->setCurPage($searchCriteria->getCurrentPage());
		$collection->setPageSize($searchCriteria->getPageSize());

		//$ordersData = $collection->load()->toArray();

		$ordersData = array ();

		foreach ( $collection->getItems () as $order ) {
			$ordersData [$order->getId ()] = $order->toArray ();
			$ordersData [$order->getId ()] [self::BILLING] = $this->_trackingHelper->getAddressData ( $order, self::BILLING );
			if($order->getShippingAddress()) {
			$ordersData [$order->getId ()] [self::SHIPPING] = $this->_trackingHelper->getAddressData ( $order, self::SHIPPING );
			} else {
			$ordersData [$order->getId ()] [self::SHIPPING] = '';
			}
			$ordersData [$order->getId ()] ['cart_items'] = $this->_trackingHelper->getOrderItemsInfo ( $order, true );
		}
		
		return $ordersData;
	}
}
