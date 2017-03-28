<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SaveAddressDataEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST ONESTEPCHECKOUT_ADDRESS = 'onestepcheckout';
    CONST BILLING = 'billing';
    CONST SHIPPING = 'shipping';

    protected $_trackingHelper;
    protected $_checkoutSession;
    protected $_cookieManager;
    protected $_coreSession;

    private $_apiToken;
    private $_indexName;
    private $_targetbayHost;

    public function __construct(
	        \Magento\Checkout\Model\Session $_checkoutSession,
                \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
	        \Magento\Framework\Session\Generic $coreSession,
		\Targetbay\Tracking\Helper\Data $_trackingHelper
    ){
	$this->_trackingHelper  = $_trackingHelper;
	$this->_checkoutSession = $_checkoutSession;
	$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken ();
	$this->_indexName       = $this->_trackingHelper->getApiIndex ();
	$this->_targetbayHost   = $this->_trackingHelper->getHostname ();
        $this->_cookieManager = $cookieManager;
	$this->_coreSession = $coreSession;

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
	if (! $this->_trackingHelper->canTrackPages ( self::ONESTEPCHECKOUT_ADDRESS )) {
		return false;
	}

	$this->_checkoutSession->setTitle ( 'Checkout' );
	$quote = $this->_checkoutSession->getQuote ();

	$billingInfo = $this->_trackingHelper->getAddressData ( $quote, self::BILLING );
	$this->pushPages ( $billingInfo, self::BILLING );

	$shippingInfo = $this->_trackingHelper->getAddressData ( $quote, self::SHIPPING );
	$this->pushPages ( $shippingInfo, self::SHIPPING );
	
	return;
    }
}
