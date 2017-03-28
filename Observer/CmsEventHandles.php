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
class CmsEventHandles implements ObserverInterface
{
	CONST ANONYMOUS_USER = 'anonymous';
	CONST ALL_PAGES = 'all';
	CONST PAGE_VISIT = 'page-visit';
	CONST PAGE_REFERRAL = 'referrer';
	CONST TIMEOUT = 900;

	private $apiToken;
	private $indexName;
	private $targetbayHost;

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
	}
	
	/**
	 * API Calls
	 *
	 * @param unknown $data        	
	 * @param unknown $type        	
	 */
	public function pushPages($data, $type) {
		$endPointUrl = $this->_targetbayHost . $type . $this->_apiToken;
		$data['index_name'] = $this->_indexName;
		try {
			$res = $this->_trackingHelper->postPageInfo($endPointUrl, json_encode($data));
		} catch (\Exception $e) {
			$this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
		}
	}
	
	/**
	 * Push the referrer data.
	 *
	 * @return boolean
	 */
	public function pushReferralData() {

		if(! $this->_trackingHelper->canTrackPages(self::PAGE_REFERRAL)) {
			return false;
		}
		if($referrerData = $this->_trackingHelper->getRefererData()) {
			$this->pushPages($referrerData, self::PAGE_REFERRAL);
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

		if(! $this->_trackingHelper->canTrackPages(self::PAGE_VISIT)) {
			return false;
		}
		
		// Page referrer Tracking
		$this->pushReferralData();
		
		$data = $this->_trackingHelper->visitInfo();

		$moduleName     = $requestInterface->getModuleName(); 

		if($moduleName == 'cms' || $moduleName == 'brand')
			return false;

		// Page Visit Tracking
		$this->pushPages($data, self::PAGE_VISIT);
		
		return;
	}
}
