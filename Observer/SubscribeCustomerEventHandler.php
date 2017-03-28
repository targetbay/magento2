<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SubscribeCustomerEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST SUBSCRIBE_CUSTOMER = 'user-subscribe';

    CONST STATUS_SUBSCRIBED = 1;
    CONST STATUS_NOT_ACTIVE = 2;
    CONST STATUS_UNSUBSCRIBED = 3;
    CONST STATUS_UNCONFIRMED = 4;

    protected $_trackingHelper;
    protected $_customerSession;
    protected $_request;
    protected $_cookieManager;
    protected $_coreSession;
    protected $_storeManager;

    private $_apiToken;
    private $_indexName;
    private $_targetbayHost;

    public function __construct(
	        \Magento\Customer\Model\Session $_customerSession,
                \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
	        \Magento\Framework\Session\Generic $coreSession,
		\Targetbay\Tracking\Helper\Data $_trackingHelper,
		\Magento\Store\Model\StoreManagerInterface $_storeManager,
		\Magento\Framework\App\RequestInterface  $request
    ){
	$this->_trackingHelper  = $_trackingHelper;
	$this->_customerSession = $_customerSession;
	$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
	$this->_indexName       = $this->_trackingHelper->getApiIndex();
	$this->_targetbayHost   = $this->_trackingHelper->getHostname();
        $this->_cookieManager = $cookieManager;
	$this->_coreSession = $coreSession;
	$this->_storeManager  = $_storeManager;
        $this->_request = $request;

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
		$res = $this->_trackingHelper->postPageInfo( $endPointUrl, json_encode( $data ));
	} catch ( Exception $e ) {
		$this->_trackingHelper->debug( " '$type' ERROR:" . $e->getMessage () );
	}
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	if (! $this->_trackingHelper->canTrackPages( self::SUBSCRIBE_CUSTOMER )) {
		return false;
	}
	
	$webstieId = $this->_storeManager->getStore()->getWebsiteId();
	$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();
	$customerModel = $objectmanager->get('Magento\Customer\Model\Customer');

	$this->_customerSession->setTitle( 'Newsletter Subscription' );

	if($this->_request->getParam( 'email' )) {
		$email = $this->_request->getParam( 'email' );
		$customer = $customerModel->setWebsiteId($webstieId)->loadByEmail($email);
		$customerId = $customer->getEntityId();
	} else {
		$customerId = $this->_customerSession->getCustomer()->getId();
		$email = '';
	}

	$data = $this->_trackingHelper->visitInfo();
	
	if(empty($email)) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customer_data =  $objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
		$subscriberFactory = $objectManager->create('Magento\Newsletter\Model\Subscriber')->loadByCustomerId($customerId);

		if ($subscriberFactory->getSubscriberStatus() == self::STATUS_UNSUBSCRIBED) {
			$status = 'Unsubscribed';
		} elseif ($subscriberFactory->getSubscriberStatus() == self::STATUS_SUBSCRIBED) {
			$status = 'Subscribed';
		} elseif ($subscriberFactory->getSubscriberStatus() == self::STATUS_UNCONFIRMED) {
			$status = 'Unconfirmed';
		} elseif ($subscriberFactory->getSubscriberStatus() == self::STATUS_NOT_ACTIVE) {
			$status = 'Not Activated';
		} else {
			$status = $subscriberFactory->getSubscriberStatus();
		}
	} else {$status = '';}

	$status = ! empty ($email) ? 'Subscribed' : $status; 	
	$data ['user_mail'] = $this->_customerSession->isLoggedIn() ? $this->_customerSession->getCustomer()->getEmail() : $email;
	$data ['subscription_status'] = $status;
	$this->pushPages ( $data, self::SUBSCRIBE_CUSTOMER);
	
	return;
    }
}
