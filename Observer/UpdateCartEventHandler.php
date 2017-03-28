<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class UpdateCartEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST UPDATECART = 'update-cart';

    protected $_productRepository;
    protected $_cart;
    protected $_trackingHelper;
    protected $_checkoutSession;
    protected $_cookieManager;
    protected $_coreSession;
    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper,
		\Magento\Catalog\Model\ProductRepository $productRepository, 
		\Magento\Checkout\Model\Cart $cart, 
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Magento\Checkout\Model\Session $_checkoutSession
    ){
	$this->_trackingHelper  = $trackingHelper;
        $this->_productRepository = $productRepository;
        $this->_cart = $cart;
        $this->_checkoutSession = $_checkoutSession;
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
     * Capture the Update cart event
     *
     * @param Varien_Event_Observer $observer        	
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	if (! $this->_trackingHelper->canTrackPages ( self::UPDATECART )) {
		return false;
	}
	$items = $this->_checkoutSession->getQuote()->getAllVisibleItems();
	$requestInfo = $observer->getEvent ()->getInfo ();
	$data = $this->_trackingHelper->getCartInfo ();

	foreach ( $items as $item ) {
		$newQty = $requestInfo [$item->getId ()] ['qty'];
		$oldQty = $item->getQty ();
		if ($newQty == 0 || ($newQty == $oldQty)) {
			continue;
		}
		$itemData = $this->_trackingHelper->getItemInfo ( $item );
		unset ( $itemData ['quantity'] );
		$itemData ['old_quantity'] = $oldQty;
		$itemData ['new_quantity'] = $newQty;
		$data ['cart_items'] [] = $itemData;
	}
	if (isset ( $data ['cart_items'] )) {
		$this->pushPages ( $data,  self::UPDATECART  );
	}
	
	return;        
    }
}
