<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class AddWishlistEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST WISHLIST = 'wishlist';

    protected $_productRepository;
    protected $_customerSession;
    protected $_trackingHelper;
    protected $_cookieManager;
    protected $_coreSession;

    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $_trackingHelper,
		\Magento\Catalog\Model\ProductRepository $_productRepository,  
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Magento\Customer\Model\Session $_customerSession
    ){
	$this->_trackingHelper  = $_trackingHelper;
        $this->_productRepository = $_productRepository;
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
	if (! $this->_trackingHelper->canTrackPages(self::WISHLIST)) {
		return false;
	}
	$this->_customerSession->setTitle('My Wishlist');
	$wishlistItems = $observer->getEvent()->getItems();
	$item_info = array();
	foreach($wishlistItems as $item) {
		if ($item->getParentItem())
			$item = $item->getParentItem();

		$item_info = $this->_trackingHelper->getWishlistProductInfo($item->getData('product_id'));
		$data = array_merge($this->_trackingHelper->visitInfo(), $item_info);
		$data ['item_id'] = $item->getWishlistItemId();
		if ($customOptions = $this->_trackingHelper->getCustomOptionsInfo($item, null)) {
			$data ['attributes'] = $customOptions;
		}
		$this->pushPages($data, self::WISHLIST);
	}

	return;
    }
}
