<?php
    
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Class EventHandles
 *
 * Custom handles to the page
 *
 * @package Targetbay\Tracking\Observer
 */
class EventHandles implements ObserverInterface
{
	CONST ANONYMOUS_USER = 'anonymous';
	CONST ALL_PAGES = 'all';
	CONST PAGE_VISIT = 'page-visit';
	CONST PAGE_REFERRAL = 'referrer';
	CONST TIMEOUT = 900;
	CONST VISTOR_ID = 6000000;

	private $apiToken;
	private $indexName;
	private $targetbayHost;
    	protected $_request;

	/**
	 * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
	 */
	protected $_trackingHelper;

	/**
	 * @var \Magento\Framework\Stdlib\CookieManagerInterface
	 */
	protected $_cookieManager;

	/**
	 * @var \Magento\Framework\Session\Generic
	 */
	protected $_coreSession;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @param \Targetbay\Tracking\Helper\Data $trackingHelper
	 * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
	 * @param \Magento\Framework\Session\Generic $coreSession
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 */
	public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper,	
                \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
	        \Magento\Framework\Session\Generic $coreSession,
	        \Magento\Customer\Model\Session $customerSession,
	        \Magento\Checkout\Model\Session $checkoutSession
	)
	{
		$this->_trackingHelper  = $trackingHelper;
        	$this->_cookieManager = $cookieManager;
		$this->_coreSession = $coreSession;
		$this->_customerSession = $customerSession;
		$this->_checkoutSession = $checkoutSession;
		$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken();
		$this->_indexName       = $this->_trackingHelper->getApiIndex();
		$this->_targetbayHost   = $this->_trackingHelper->getHostname();

		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();			
		$coreSession = $objectmanager->create('Magento\Framework\Session\SessionManagerInterface');

		if(empty($this->_cookieManager->getCookie('trackingsession'))) {
			$visitor_data    = $coreSession->getVisitorData();
            		$vistorId = $visitor_data['visitor_id'] . strtotime ( date ( 'Y-m-d H:i:s' ) );
			$trackingsession = $vistorId;
			$coreSession->setTrackingSessionId($trackingsession);
		}
	}
	
	/**
	 * Set the cookie values for user differentiate.
	 */
	public function setCookieValues() {

		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();			
		$coreSession = $objectmanager->get('Magento\Framework\Session\Generic');
		$_cookieMetadata = $objectmanager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');

		// For anonymous user
		$customerName = self::ANONYMOUS_USER;
		$customerEmail = self::ANONYMOUS_USER;

		if(!$this->_cookieManager->getCookie('trackingid')) {
			$customerId = $coreSession->getTrackingSessionId();
		}		

		$metadata = $_cookieMetadata
				  ->createPublicCookieMetadata()
				  ->setDuration(self::TIMEOUT)
				  ->setPath('/')
				  ->setDomain($coreSession->getCookieDomain())
            			  ->setHttpOnly(false);
		
		// for logged user
		if($this->_customerSession->isLoggedIn ()) {
			$customer = $this->_customerSession->getCustomer();
			$customerName = $customer->getName();
			$customerId = $customer->getId();
			$customerEmail = $customer->getEmail();
			$this->_cookieManager->setPublicCookie('user_loggedin', true, $metadata);
			$this->_cookieManager->setPublicCookie('afterlogin_session_id', $coreSession->getCustomerSessionId(), $metadata);
		}
		
		// ToDo: Do we need this?
		$trackingId = empty($customerId) ? '' : $this->_cookieManager->setPublicCookie('trackingid', $customerId, $metadata);
		
		$this->_cookieManager->setPublicCookie('trackingemail', $customerEmail, $metadata);
		$this->_cookieManager->setPublicCookie('trackingname', $customerName, $metadata);

		$quoteId = $this->_checkoutSession->getQuoteId() ? $this->_checkoutSession->getQuoteId() : '';
		$this->_cookieManager->setPublicCookie('trackingorderid', $quoteId, $metadata);
		
		if (! $this->_cookieManager->getCookie('trackingsession')) {
			$this->_cookieManager->setPublicCookie('trackingsession', $coreSession->getTrackingSessionId(), $metadata);
		}
	}

	/**
	 * Visiting page info
	 *
	 * @param Observer $observer
	 * @event controller_action_postdispatch
	 *
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer) {

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');			
		$coreSession = $objectManager->get('Magento\Framework\Session\Generic');
		$_cookieMetadata = $objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');

		$metadata = $_cookieMetadata
				  ->createPublicCookieMetadata()
				  ->setDuration(self::TIMEOUT)
				  ->setPath('/')
				  ->setDomain($coreSession->getCookieDomain())
            			  ->setHttpOnly(false);

		$this->setCookieValues();
		// Set Token Values
		if(($requestInterface->getParam('utm_source') != '') && ! $this->_cookieManager->getCookie('utm_source')) {
			$this->_cookieManager->setPublicCookie('utm_source', $requestInterface->getParam('utm_source'), $metadata);
		}
		
		if(($requestInterface->getParam('token') != '') && ! $this->_cookieManager->getCookie('utm_token')) {
			$this->_cookieManager->setPublicCookie ('utm_token', $requestInterface->getParam('token'), $metadata);
		}
		
		return;
	}
}
