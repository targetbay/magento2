<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\TotalProductInterface;

/**
 * Defines the implementaiton class of the TotalProduct.
 */
class TotalProduct implements TotalProductInterface
{

	/**
	 * Get the Total Products
	 *
	 * @return totals
	 */
	public function totalproductcount() {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$productFactory = $objectManager->create('Magento\Catalog\Model\ProductFactory');
		//$productVisibility = $objectManager->get('Magento\Catalog\Model\Product\Visibility');

		$collection = $productFactory->create()->getCollection();
		//$collection = $productFactory->create();
		//$collection->addAttributeToFilter('status', ['in' => $productVisibility->getVisibleInSiteIds()]);
		//$collection->setVisibility($productVisibility->getVisibleStatusIds());
		//$collection->getCollection();
		//echo 'sql=='.$collection->getSelect()->__toString();
		$totals = array (
					'total_products' =>  $collection->getSize()
		);
		
		return json_encode($totals);
	}
}
