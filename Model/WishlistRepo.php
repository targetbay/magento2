<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\WishlistRepoInterface;

/**
 * Defines the implementaiton class of the WishlistRepo.
 */
class WishlistRepo implements WishlistRepoInterface
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
	 * get wishlist items.
	 *
	 * @return WishlistRepoInterface[]
	 */
	public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria) {

		$customerId = '';
		$wishlistData = array();

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$wishlistCollection = $objectManager->create('Magento\Wishlist\Model\Wishlist')->getCollection();
		foreach($wishlistCollection as $id => $wishlist) {
			$wishlistInfo = $objectManager->create('Magento\Wishlist\Model\Wishlist')->loadByCustomerId($wishlist->getCustomerId());
			$wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();
			if ($wishlistItemCollection->getSize() > 0) {
				$wishlistData[$id]['wishlist_id'] = $wishlistInfo['wishlist_id'];
				$wishlistData[$id]['customer_id'] = $wishlistInfo['customer_id'];
				$wishlistData[$id]['updated_at'] = $wishlistInfo['updated_at'];
				$wishlistData[$id]['item_details'] = $this->_trackingHelper->getWishlistItemsInfo ( $wishlistInfo); 
			}
		}
		return  $wishlistData;
	}
}
