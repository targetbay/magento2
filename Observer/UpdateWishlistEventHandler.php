<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class UpdateWishlistEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST UPDATE_WISHLIST = 'update-wishlist';

    protected $_request;
    protected $_trackingHelper;
    protected $_customerSession;
    protected $_cookieManager;
    protected $_coreSession;

    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $_trackingHelper,
		\Magento\Framework\App\RequestInterface $_request, 
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Magento\Customer\Model\Session $_customerSession
    ){
	$this->_trackingHelper  = $_trackingHelper;
        $this->_request = $_request;
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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	if (! $this->_trackingHelper->canTrackPages ( self::UPDATE_WISHLIST )) {
		return false;
	}

	$this->_customerSession->setTitle ( 'My Wishlist' );

	$wishlistId = $this->_request->getParam('wishlist_id');
	$wishlistDesc = $this->_request->getParam('description');
	$wishlistQty = $this->_request->getParam('qty');
	$data = $this->_trackingHelper->visitInfo ();
	$items = array();
	$data ['wishlist_id'] = $wishlistId;
	
	foreach($wishlistDesc as $id => $item) {
            	$wishlistItem = $objectManager->get('\Magento\Wishlist\Model\Item')->load($id);
		$items[$id]['item_id'] = $id;
		$items[$id]['product_id'] = $wishlistItem->getProductId();
		$items[$id]['store_id'] = $wishlistItem->getStoreId();
		$items[$id]['description'] = $item;
		$items[$id]['qty'] = $wishlistQty[$id];
	}		
	
	$data['wishlist_items'] = $items;
	$this->pushPages ( $data, self::UPDATE_WISHLIST);

    }
}
