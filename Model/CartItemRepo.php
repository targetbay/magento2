<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CartItemRepoInterface;

/**
 * Defines the implementaiton class of the CartItemRepo.
 */
class CartItemRepo implements CartItemRepoInterface
{

	/**
	 * @var \Magento\Framework\App\RequestInterface $_request
	 */
	protected $_request;

	/**
	 * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
	 */
	protected $_trackingHelper;	

	/**
	 * @var \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
	 */
	protected $_customerRepository;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
	 */
	protected $_storeManager;

	public function __construct(
		\Magento\Framework\App\RequestInterface $_request,
		\Targetbay\Tracking\Helper\Data $_trackingHelper,
		\Magento\Store\Model\StoreManagerInterface $_storeManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $_customerRepository
	){
		$this->_request = $_request;
		$this->_trackingHelper  = $_trackingHelper;
		$this->_storeManager  = $_storeManager;
		$this->_customerRepository  = $_customerRepository;
	}

	/**
	 * get customer cart items.
	 *
	 * @return CartItemRepoInterface[]
	 */
	public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria) {

		$cartItems = $cartItemData = array();
		$quoteCollection = $this->getQuoteCollectionQuery($searchCriteria);
		$cartItems = array();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		foreach ($quoteCollection as $id => $quoteInfo) {
			$quoteId = $quoteInfo['entity_id'];
			$quote = $objectManager->create('Magento\Quote\Model\Quote')->setId($quoteId);
			$items = $quote->getAllVisibleItems();
			if(count($items) > 0) {
				$cartItems[$id]['entity_id'] = $quoteInfo['entity_id'];
				$cartItems[$id]['customer_id'] = $quoteInfo['customer_id'];
				$cartItems[$id]['customer_email'] = $quoteInfo['customer_email'];
				$cartItems[$id]['abandonded_at'] = $quoteInfo['updated_at'];
				$cartItems[$id]['cart_items'] = $this->_trackingHelper->getQuoteItems ( $items); 
			}
		}
		
		return $cartItems;
	}

	/**
	 * get quote collection.
	 *
	 * @return CartItemRepoInterface[]
	 */
	public function getQuoteCollectionQuery($searchCriteria) {
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
		
            	$quoteCollection->setCurPage($searchCriteria->getCurrentPage());
		$quoteCollection->setPageSize($searchCriteria->getPageSize());
		return $quoteCollection;
	}
}
