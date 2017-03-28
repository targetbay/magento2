<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class UserLoginEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST LOGIN = 'login';

    protected $_trackingHelper;
    protected $_cookieManager;
    protected $_coreSession;
    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Targetbay\Tracking\Helper\Data $trackingHelper
    ){
	$this->_trackingHelper  = $trackingHelper;
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
    {	$this->_trackingHelper->debug ( "customer login");
	if (! $this->_trackingHelper->canTrackPages ( self::LOGIN )) {	$this->_trackingHelper->debug ( "canTrackPages");
		return false;
	}
	if (! $observer->getCustomer ()) {	$this->_trackingHelper->debug ( "observer");
		return false;
	}	
$this->_trackingHelper->debug ( "self::LOGIN");
	$data = $this->_trackingHelper->getCustomerData ( $observer->getCustomer (), self::LOGIN );
	$this->pushPages ( $data, self::LOGIN );
	
	return;
    }
}
