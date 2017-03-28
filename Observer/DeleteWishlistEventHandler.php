<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class DeleteWishlistEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST REMOVE_WISHLIST = 'remove-wishlist';

    protected $_request;
    protected $_trackingHelper;
    protected $_itemFactory;
    protected $_wishlistFactory;
    protected $_customerSession;
    protected $_cookieManager;
    protected $_coreSession;

    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $_trackingHelper,
		\Magento\Framework\App\RequestInterface $_request,
		\Magento\Wishlist\Model\ItemFactory $_itemFactory,
		\Magento\Wishlist\Model\WishlistFactory $_wishlistFactory,
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession, 
		\Magento\Customer\Model\Session $_customerSession
    ){
	$this->_trackingHelper  = $_trackingHelper;
        $this->_request = $_request;
        $this->_itemFactory = $_itemFactory;
        $this->_wishlistFactory = $_wishlistFactory;
        $this->_customerSession = $_customerSession;

	$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken ();
	$this->_indexName       = $this->_trackingHelper->getApiIndex ();
	$this->_targetbayHost   = $this->_trackingHelper->getHostname ();
        $this->_cookieManager = $_cookieManager;
	$this->_coreSession = $_coreSession;

	/*if (! $this->_cookieManager->getCookie ( 'trackingsession' )) {
		$trackingsession = $this->_coreSession->getVisitorData ()['visitor_id'];
		$this->_coreSession->setTrackingSessionId ( $trackingsession );
	}*/
    }
	
    /**
     * API Calls
     *
     * @param unknown $data        	
     * @param unknown $type        	
     */
    public function pushPages($data, $type) {
	$endPointUrl = $this->_targetbayHost . $type . $this->_apiToken;
	$data ['index_name'] = $this->_indexName;
	try {
		$res = $this->_trackingHelper->postPageInfo ( $endPointUrl, json_encode ( $data ) );
	} catch ( Exception $e ) {
		$this->_trackingHelper->debug ( " '$type' ERROR:" . $e->getMessage () );
	}
    }
	
    /**
     * Capture the remove cart item data
     *
     * @param \Magento\Framework\Event\Observer $observer        	
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	if(! $this->_trackingHelper->canTrackPages ( self::REMOVE_WISHLIST )) {
		return false;
	}

	$this->_customerSession->setTitle ( 'My Wishlist' );
	$itemId = (int) $this->_request->getParam('item');
	$item = $this->_itemFactory->create()->load($itemId);

	if(!$item->getId()) {
	    	return false;
	}

	$wishlist = $this->_wishlistFactory->create()->load($item->getWishlistId());

	if(!$wishlist) {
	    	return false;
	} else {
		$data = $this->_trackingHelper->visitInfo();
		$data['item_id'] = $itemId;
		$data['product_id'] = $wishlist->getProductId();
		$data['store_id'] = $wishlist->getStoreId();
		$data['wishlist_id'] = $item->getWishlistId();
		$this->pushPages ($data, self::REMOVE_WISHLIST);
	}

	return;    
    }
}
