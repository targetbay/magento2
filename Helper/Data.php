<?php
/*
 * @author Targetbay
 * @copyright 2016 Sathishkumar Mariappan <sathishkumar.m@innoppl.com>
 * @package Targetbay_Tracking
 */
namespace Targetbay\Tracking\Helper;

/**
 * Class Data
 *
 * Custom helper class
 *
 * @package Targetbay\Tracking\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{	
	CONST ANONYMOUS_USER = 'anonymous';
	CONST ALL_PAGES = 'all';
	CONST PAGE_VISIT = 'page-visit';
	CONST PAGE_REFERRAL = 'referrer';
	CONST ADDTOCART = 'add-to-cart';
	CONST BILLING = 'billing';
	CONST SHIPPING = 'shipping';
	CONST TIMEOUT = 900;
	
	// order fullfillment process
	CONST ORDER_SHIPMENT = 'shipment';
	CONST ORDER_INVOICE = 'invoice';
	CONST ORDER_REFUND = 'creditmemo';
	
	CONST CREATE_ACCOUNT = 'create-account';
	CONST ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';
	CONST LOGIN = 'login';
	CONST LOGOUT = 'logout';
    
	// product stock status
	CONST IN_STOCK = 'in-stock';
	CONST OUT_OF_STOCK = 'out-stock';

	CONST MODULE_NAME = 'Targetbay_Tracking';
	CONST RATINGS_STATS = 'ratings-stats';
    CONST QUESTION_STATS = 'qa-stats';
	
	CONST VISTOR_ID = 6000000;

	CONST STATUS_SUBSCRIBED = 1;
	CONST STATUS_NOT_ACTIVE = 2;
	CONST STATUS_UNSUBSCRIBED = 3;
	CONST STATUS_UNCONFIRMED = 4;	

	CONST HOST_STAGE = 'https://stage.targetbay.com/api/v1/webhooks/';
	CONST HOST_LIVE = 'https://app.targetbay.com/api/v1/webhooks/';
	CONST HOST_DEV = 'https://dev.targetbay.com/api/v1/webhooks/';
	
	CONST API_STAGE = 'stage';
	CONST API_LIVE = 'app';
	CONST API_DEV = 'dev';

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $_scopeConfig;	

	/**
	 * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
	 */
	protected $_countryInformation;

	/**
	 * @var \Magento\Framework\Stdlib\CookieManagerInterface
	 */
	protected $_cookieManager;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_date;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $_urlHelper;

	/**
	 * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
	 */
	protected $_remoteAddress;

	/**
	 * @var \Magento\Framework\HTTP\Header
	 */
	protected $_httpHeader;

	/**
	 * @var \Magento\Framework\View\Page\Title
	 */
	protected $_pageTitle;

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
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	public $_storeManager;

	/**
	 * @var \Magento\Framework\HTTP\ZendClientFactory
	 */
	protected $_httpClientFactory;

	/**
	 * @var \Magento\Framework\UrlInterface
	 */
    protected $_urlInterface;

	/**
	 * @var \Magento\Framework\Session\Generic
	 */
	protected $session;

	/**
	 * @var \Magento\Framework\Module\ModuleListInterface
	 */
	protected $_moduleList;

	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	/**
	 * @var \Magento\Framework\HTTP\Client\Curl
	 */
	protected $_curl;

	/**
	 * @var list of modules
	 */
	public $moduleNameArray = ['catalogsearch', 
				   'newsletter', 
				   'checkout', 
				   'sales', 
				   'customer', 
				   'wishlist', 
				   'review',
				   'downloadable',
				   'rma',
				   'reward',
				   'giftcard',
				   'storecredit'
				  ];


	/**
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
	 * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
	 * @param \Magento\Framework\Url\Helper\Data $urlHelper
	 * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
	 * @param \Magento\Framework\HTTP\Header $httpHeader
	 * @param \Magento\Framework\View\Page\Title $pageTitle
	 * @param \Magento\Framework\Session\Generic $session
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
         * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
	 * @param \Magento\Framework\UrlInterface $urlInterface
         * @param \Psr\Log\LoggerInterface $logger,
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList,
	 * @param \Magento\Framework\HTTP\Client\Curl $curl
	 */
	
	public function __construct(
            \Magento\Framework\Stdlib\DateTime\DateTime $date,	
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
	    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	    \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
	    \Magento\Framework\Url\Helper\Data $urlHelper,
	    \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
            \Magento\Framework\HTTP\Header $httpHeader,
            \Magento\Framework\View\Page\Title $pageTitle,
	    \Magento\Framework\Session\Generic $coreSession,
	    \Magento\Customer\Model\Session $customerSession,
	    \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
            \Magento\Framework\UrlInterface $urlInterface,
	    \Magento\Framework\Session\Generic $session,
            \Psr\Log\LoggerInterface $logger,
	    \Magento\Framework\Module\ModuleListInterface $moduleList,
	    \Magento\Framework\HTTP\Client\Curl $curl
	) {
        	$this->_cookieManager = $cookieManager;
        	$this->_date = $date;
        	$this->_scopeConfig = $scopeConfig;
        	$this->_countryInformation = $countryInformation;
        	$this->_urlHelper = $urlHelper;
        	$this->_remoteAddress = $remoteAddress;
        	$this->_httpHeader = $httpHeader;
        	$this->_pageTitle = $pageTitle;
		$this->_coreSession = $coreSession;
		$this->_customerSession = $customerSession;
		$this->_checkoutSession = $checkoutSession;
		$this->_storeManager = $storeManager;
        	$this->_httpClientFactory = $httpClientFactory;        
		$this->_urlInterface = $urlInterface;       
		$this->_session = $session;
		$this->logger = $logger;
		$this->_moduleList = $moduleList;	
	}
	
	/**
	 * Check module is enabled or not
	 *
	 * @return boolean
	 */
	public function trackingEnabled() {
		return (bool) $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Get Target bay Host
	 *
	 * @return mixed
	 */
	public function getHostname() {
		return self::HOST_LIVE;
	}
	
	/**
	 * Get TargetbayToken
	 *
	 * @return mixed
	 */
	public function getApiToken() {
		return  ( string ) $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/api_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Get TargetBayIndex
	 *
	 * @return mixed
	 */
	public function getApiIndex() {
		return  ( string ) $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/api_index', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Enabled the log or not
	 *
	 * @return string
	 */
	public function logEnabled() {
		return $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/debug', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Get the log file name from configurations
	 */
	public function getLogFileName() {
		return $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/debug_file', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Get the availabe pages from configurations
	 *
	 * @return multitype:
	 */
	public function availablePageTypes() {
		$types = (string) $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/page_types', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$typesArray = explode(',', $types);
		return $typesArray;
	}
	
	/**
	 * Get getApiStatus
	 *
	 * @return mixed
	 */
	public function getApiStatus() {
		return self::API_LIVE;
	}
	
	/**
	 * Get the Session Tracking Js
	 *
	 * @return mixed
	 */
	public function getReviewPageSize() {
		    
		$reviewSize = $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/reviews_per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($reviewSize){
			$reviewSize = $reviewSize;
		} else {
			$reviewSize = 10;
		}
		return $reviewSize;
	}
	
	/**
	 * Customer welcome email and newsletter email enabled or not
	 *
	 * @return string
	 */
	public function getEmailStatus() {
		$emailStatus = $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/disable_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		if($emailStatus){
			$emailStatus = $emailStatus;
		} else {
			$emailStatus = 1;
		}

		return $emailStatus;
	}
	
	/**
	 * Get the Module version
	 *
	 * @return mixed
	 */
	public function getModuleVersion() {
		return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
	}
    
	/**
	 * Get Tracking Code
	 *
	 * @return mixed
	 */
	public function getTrackingScript()
	{
		return $this->_scopeConfig->getValue('targetbay_tracking/tracking_groups/tracking_script', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Check coupon code generation is enabled or not
	 *
	 * @return boolean
	 */
	public function getCouponCodeStatus() {
		return $this->_scopeConfig->getValue('targetbay_tracking/coupon_configuration/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Get coupon code length
	 *
	 * @return int
	 */
	public function getCouponCodeLength() {
		return $this->_scopeConfig->getValue('targetbay_tracking/coupon_configuration/length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Get coupon code format
	 *
	 * @return string
	 */
	public function getCouponCodeFormat() {
		return $this->_scopeConfig->getValue('targetbay_tracking/coupon_configuration/format', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * Check the page configurations
	 *
	 * @param unknown $pageType        	
	 * @return boolean
	 */
	public function canTrackPages($pageType) {
		if(!$this->trackingEnabled()) {
			$this->debug('Tracking Module is Not Enabled. Please enable a Module');
			return false;
		}
		$availabelPages = array('all');
		if(in_array(self::ALL_PAGES, $availabelPages)) {
			return true;
		}
		if(! in_array($pageType, $availabelPages)) {
			$this->debug("'$pageType'" . 'page is not enabled');
			return false;
		}
		return true;
	}
	
	/**
	 * Basic visit info
	 *
	 * @return string
	 */
	public function visitInfo() {
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();		
		$coreSession = $objectmanager->get('Magento\Framework\Session\Generic');
		$urlInterface = $objectmanager->get('Magento\Framework\UrlInterface');
		$requestInterface = $objectmanager->get('Magento\Framework\App\RequestInterface');
		$customerObj = $objectmanager->get('Magento\Customer\Model\Customer');
		$moduleName     = $requestInterface->getModuleName();
		$customerName = '';
		$data = array();

		try {
			if(in_array($moduleName, $this->moduleNameArray) && ($this->_customerSession->isLoggedIn())) {
			    $user_id = $this->_customerSession->getCustomerId();
			} else {
			    $user_id = $this->_cookieManager->getCookie('targetbay_session_id');
			}

			if($this->_customerSession->isLoggedIn()) {
				$customer_data = $customerObj->load($this->_customerSession->getCustomerId());
				$customerName = $customer_data->getFirstname().' '.$customer_data->getLastname();
			}

			$data['user_id']     = $user_id;
			$data['session_id']  = $this->_customerSession->isLoggedIn() ? (string)$coreSession->getCustomerSessionId() : (string)$this->_cookieManager->getCookie('targetbay_session_id');

			$data['user_name']   = $this->_customerSession->isLoggedIn() ? $customerName : self::ANONYMOUS_USER;
			$data['page_url']    = $urlInterface->getCurrentUrl();
			$data['ip_address']  = $this->_remoteAddress->getRemoteAddress();
			$data['user_agent']  = $this->_httpHeader->getHttpUserAgent();
			$data['utm_sources'] = $this->_cookieManager->getCookie('utm_source') ? $this->_cookieManager->getCookie('utm_source') : '';
			$data['utm_token']   = $this->_cookieManager->getCookie('utm_token') ? $this->_cookieManager->getCookie('utm_token') : '';
			$pageTitle = $this->_pageTitle->getShort() ? $this->_pageTitle->getShort() : $this->_checkoutSession->getTitle();
			$data['page_title']  = $objectmanager->get('Magento\Framework\Escaper')->escapeQuote($pageTitle);
		} catch (\Exception $e) {
			$this->logger->critical($e);
		}
		return $data;
	}
	
	/**
	 * Push the referer url
	 *
	 * @return boolean
	 */
	public function getRefererData() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
		$isSecure = $requestInterface->isSecure();
        $domainName = $_SERVER['SERVER_NAME'];
		if($isSecure) {
			$currentUrl = $this->_storeManager->getStore()->getUrl('', array('_secure'=>true));
		} else {
			$currentUrl = $this->_storeManager->getStore()->getUrl('');
		}
		$referer = $this->_httpHeader->getHttpReferer();
		if (strpos($referer, $domainName ) !== false) {
			return false; // base url and referer url matches.
		}
		$data = $this->visitInfo();
		$data['referrer_url'] = $referer;
		return $data;
	}
	
	/**
	 * Stop the page visit for already tracked events
	 *
	 * @return multitype:string
	 */
	public function eventAlreadyTracked($eventName) {
		$stopAction = array(
				'cms_index_index',
				'catalog_category_view',
				'catalog_product_view',
				'catalogsearch_result_index',
				'catalogsearch_ajax_suggest',
				'checkout_cart_add',
				'checkout_cart_index',
				'checkout_cart_updatePost',
				'checkout_cart_remove',
				'customer_account_create',
				'customer_account_loginPost',
				'customer_account_logout',
				'customer_account_logoutSuccess',
				'customer_account_createPost',
				'checkout_onepage_index',
				'checkout_onepage_saveBilling',
				'checkout_onepage_saveShipping',
				'checkout_onepage_saveShippingMethod',
				'checkout_onepage_savePayment',
				'checkout_onepage_getAdditional',
				'checkout_onepage_progress',
				'checkout_onepage_saveOrder' 
		);
		return in_array($eventName, $stopAction);
	}
	
	/**
	 * Push the page info using Varien_Http_Client
	 *
	 * @return string
	 */
	public function postPageInfo($url, $jsondata) {
		$client = $this->_httpClientFactory->create();
		$client->setUri((string)$url);
		$client->setConfig(array(
				'maxredirects' => 0,
				'timeout' => 1,
				'useragent' => 'Zend_Http_Client',
				'httpversion' => 'Http_1.1',
				'adapter' => 'Zend_Http_Client_Adapter_Socket',
				'keepalive' => 'true'
		));
		$client->setRawData(utf8_encode($jsondata));
		$response = '';
		try {
			$this->debug("Endpoint Url =>".$url);
			$this->debug("Request =>");
			$this->debug($jsondata);
			$response = $client->request(\Zend_Http_Client::POST)->getBody();
			$this->debug("Response =>");
			$this->debug($response);
		} catch (\Exception $e) {
			$this->logger->critical($e);
		}
		return $response;
	}
	
	/**
	 * Get the common info for quote or order ,billing and shipping
	 *
	 * @param unknown $order        	
	 * @return string
	 */
	public function getSessionInfo($object) {
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();		
		$coreSession = $objectmanager->get('Magento\Framework\Session\Generic');

		$data['session_id'] = $this->_customerSession->isLoggedIn() ? (string)$coreSession->getCustomerSessionId() : (string)$this->_cookieManager->getCookie('targetbay_session_id');
		$data['user_id'] = $object->getCustomerId() ? (string)$object->getCustomerId() : (string)$this->_cookieManager->getCookie('targetbay_session_id');

		$data['utm_sources'] = $this->_cookieManager->getCookie('utm_source') ? $this->_cookieManager->getCookie('utm_source') : '';
		$data['utm_token'] = $this->_cookieManager->getCookie('utm_token') ? $this->_cookieManager->getCookie('utm_token') : '';
		$data['user_agent']  = $this->_httpHeader->getHttpUserAgent();
		$data['timestamp'] = strtotime($this->_date->date('Y-m-d'));
		$pageTitle = $this->_pageTitle->getShort() ? $this->_pageTitle->getShort() : $this->_checkoutSession->getTitle();
		$data['page_title']  = $objectmanager->get('Magento\Framework\Escaper')->escapeQuote($pageTitle);
		return $data;
	}
	
	/**
	 * Get the cart info
	 *
	 * @return string
	 */
	public function getCartInfo() {
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();		
		$coreSession = $objectmanager->get('Magento\Framework\Session\Generic');
		$checkout = $this->_checkoutSession->getQuote();
		$data['user_name']  = self::ANONYMOUS_USER;
		$data['user_mail']  = self::ANONYMOUS_USER;
		$data['user_id']    = $this->_cookieManager->getCookie('targetbay_session_id');
		$data['session_id'] = $this->_customerSession->isLoggedIn() ? (string)$coreSession->getCustomerSessionId() : (string)$this->_cookieManager->getCookie('targetbay_session_id');
		$data['user_agent']  = $this->_httpHeader->getHttpUserAgent();
		if ($this->_customerSession->isLoggedIn()) {
			$data['user_name'] = $this->_customerSession->getCustomer()->getName();
			$data['user_mail'] = $this->_customerSession->getCustomer()->getEmail();
			$data['user_id']   = $this->_customerSession->getId();
		}
		$data['order_id']   = $this->_checkoutSession->getQuoteId();
		$data['timestamp']  = strtotime($this->_date->date('Y-m-d'));
		
		return $data;
	}
	
	/**
	 * Get the item info
	 *
	 * @param unknown $item        	
	 * @return unknown
	 */
	public function getItemInfo($item, $actionType = false) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$dataItem = array();
		try {
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getData('product_id'));
		$dataItem['type'] = $item->getProductType();
		$dataItem['product_id'] = $item->getProductId();
		$dataItem['product_name'] = $objectManager->get('Magento\Framework\Escaper')->escapeQuote($item->getName());
		$dataItem['product_sku'] = $product->getSku();
		$dataItem['msrp_price'] = $product->getMsrp() ? $product->getMsrp() : '';
		$dataItem['price'] = $actionType ? $item->getProduct()->getPrice() : $item->getPrice();
		$qty = $item->getQty();
		$dataItem['productimg'] = $this->getImageUrl($product, 'image');
		$dataItem['abstract'] = $product->getAbstract();

		$dataItem['category'] = $this->getProductCategory($product);
		$dataItem['category_name'] = $this->getProductCategoryName($product);
		$dataItem['quantity'] = !empty($item->getData('qty_ordered')) ? $item->getData('qty_ordered') : $qty;
		$dataItem['page_url'] = $product->getUrlModel()->getUrl($product);
		} catch (\Exception $e) {
			$this->logger->critical($e);
		}
		return $dataItem;
	}
	
	/**
	 * Get the Product image url
	 *
	 * @param unknown $product
	 * @param unknown $imageType        	
	 * @return string
	 */
	public function getImageUrl($product, $imageType) {
		if($product->getData($imageType)) {
			$imgPath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getData($imageType);
		} else {
			$imgPath = '';
		}

		return $imgPath;
	}	

	/**
	 * Get the product categories
	 *
	 * @param unknown $product       	
	 * @return unknown
	 */	
	public function getProductCategory($product) {
		$categoryIds = $product->getCategoryIds();

		$categoryId = '';
		if(count($categoryIds)) {
			$categoryId = implode(',', $categoryIds);
		}
		
		return $categoryId;
	}	

	/**
	 * Get the product categories
	 *
	 * @param unknown $product       	
	 * @return unknown
	 */	
	public function getProductCategoryName($product) {
		$categoryIds = $product->getCategoryIds();

		$productCategories = array();
		$categoryName = '';
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		if(count($categoryIds)) {
			
			foreach($categoryIds as $categoryId) {		
			$_category = $objectManager->get('Magento\Catalog\Model\Category')->load($categoryId);
			$productCategories[] = $_category->getName();
			}

			$categoryName = implode(',', $productCategories);
		}

		return $objectManager->get('Magento\Framework\Escaper')->escapeQuote($categoryName);
	}	
	
	/**
	 * Get the order or quote address by address type.
	 *
	 * @param unknown $object        	
	 * @param unknown $type        	
	 * @return string
	 */
	public function getAddressData($object, $type) {
		$address = ($type == self::SHIPPING) ? $object->getShippingAddress() : $object->getBillingAddress();
		$addressData = $this->getSessionInfo($object);
		$addressData['first_name'] = $address->getFirstname();
		$addressData['last_name']  = $address->getLastname();
		$guestUsername = $address->getFirstname().' '.$address->getLastname();
		$gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
		$addressData['user_name']  = $object->getCustomerIsGuest() ? $gName : $addressData['first_name'] . ' ' . $addressData['last_name'];
		$addressData['order_id'] = $object->getId();
		$addressData['user_mail'] = $object->getCustomerEmail();
		$addressData['address1'] = $address->getStreet(1);
		$addressData['address2'] = $address->getStreet(2);
		$addressData['city'] = $address->getCity();
		$addressData['state'] = $address->getRegion();
		$addressData['zipcode'] = $address->getPostcode();
		if($address->getCountryId()) {
			$countryName = $this->_countryInformation->getCountryInfo($address->getCountryId())->getFullNameLocale();
		} else {
			$countryName = '';
		}
		$addressData['country'] = isset($countryName) ? $countryName : $address->getCountryId();
		$addressData['phone'] = $address->getTelephone();
		return $addressData;
	}
	
	/**
	 * Collect the order items info
	 *
	 * @param unknown $order        	
	 * @return multitype:unknown
	 */
	public function getOrderItemsInfo($order, $orderExportApi = false) {
		$items = $order->getAllVisibleItems();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$dataItems = array();
		foreach($items as $orderItem) {
			$product = $objectManager->get('Magento\Catalog\Model\Product')->load($orderItem->getData('product_id'));
			$productVisibility = $product->getVisibility();
			if($productVisibility != 1) {
				$dataItem = $this->getItemInfo($orderItem);
				if($customOptions = $this->getCustomOptionsInfo($orderItem, $orderExportApi))
					$dataItem['attributes'] = $customOptions;
				$dataItems[] = $dataItem;
			}
		}
		
		return $dataItems;
	}
	
	/**
	 * Get the item custom options
	 *
	 * @param unknown $item        	
	 * @return boolean|unknown|number
	 */
	public function getCustomOptionsInfo($item, $orderExportApi) {
		$customOptions = $orderExportApi ? $item->getProductOptions() : $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
		
		if(empty($customOptions['options']) && empty( $customOptions ['attributes_info'] ))
			return false;
		$superAttributeInfo = isset ( $customOptions ['attributes_info'] ) ? $this->getOptionValues ( $customOptions ['attributes_info'] ) : array ();
		$customOptionInfo = isset ( $customOptions ['options'] ) ? $this->getOptionValues ( $customOptions ['options'] ) : array ();
		$optionsData = array_merge ( $superAttributeInfo, $customOptionInfo );
		return $optionsData;
	}
	
	/**
	 * Get the options values
	 *
	 * @param unknown $options        	
	 * @return multitype:unknown
	 */
	public function getOptionValues($options) {
		$optionData = array ();
		$data = array();
		foreach ( $options as $option ) {
			$data ['label'] = $option ['label'];
			$data ['value'] = $option ['value'];
			$optionData [] = $data;
		}
		return $optionData;
	}
	
	/**
	 * debugging
	 *
	 * @param unknown $mess        	
	 */
	public function debug($mess) {
		$fileName = $this->getLogFileName ();
		if ($this->logEnabled ()) {
			$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$fileName);
			$logger = new \Zend\Log\Logger();
			$logger->addWriter($writer);
			$logger->info($mess);
		}
	}
	
	/**
	 * Check the order full fillment
	 *
	 * @return boolean
	 */
	public function isFullFillmentProcess($params) {
		if (isset ( $params [self::ORDER_SHIPMENT] ) || isset ( $params [self::ORDER_INVOICE] ) || isset ( $params [self::ORDER_REFUND] ))
			return true;
		return false;
	}
	
	/**
	 *
	 * @param unknown $order        	
	 * @param unknown $params        	
	 * @return unknown|boolean
	 */
	public function getFullFillmentData($order, $params) {
		$shipmetItems = array(); $shipmentsInfo = array();
		if(isset($params[self::ORDER_SHIPMENT])) {
			try {
				$shipmentsInfo['order_id'] = $order->getId();
				$shipmentsInfo['order_status'] = $order->getStatus();
				$shipmentsInfo['total_ordered_qty'] = $order->getData('total_qty_ordered');
				$shipmentsInfo['user_id'] = $order->getData('customer_is_guest') ? self::ANONYMOUS_USER : $order->getData ('customer_id');
				$shipmentsInfo['user_mail'] = $order->getData('customer_is_guest') ? $order->getData('customer_email') : $order->getData('customer_email');
		    		$shipmentsInfo['created_at'] = $order->getData('updated_at');
				foreach($order->getAllVisibleItems() as $item) {
					if($item->getQtyShipped() == '')
					    continue;
					$shipmentItemInfo['product_id'] = $item->getProductId();
					$shipmentItemInfo['name'] = $item->getName();
					$shipmentItemInfo['sku'] = $item->getSku();
					$shipmentItemInfo['qty_ordered'] = $item->getQtyOrdered();
					$shipmentItemInfo['qty_shipped'] = $item->getQtyShipped();
					$shipmetItems[] = $shipmentItemInfo;
				}
				$shipmentsInfo['shipment_items'] = $shipmetItems;

				return $shipmentsInfo;
			} catch (\Exception $e) {
				$this->logger->critical($e);
			}
		}
		return false;
	}
	
	/**
	 * Check the order placed by registered user or not
	 *
	 * @param unknown $quoteId        	
	 * @return boolean|Mage_Core_Model_Abstract
	 */
	public function isRegisterCheckout($order) {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$checkoutMethod = $objectManager->create('Magento\Quote\Model\Quote')->load($order->getQuoteId())->getCheckoutMethod (true);

		if($checkoutMethod != 'register')
			return false;

		return $objectManager->create('Magento\Customer\Model\Customer')->load($order->getCustomerId());
	}
	
	/**
	 * Get the customer data based on the action
	 *
	 * @param unknown $customer        	
	 * @param unknown $action        	
	 * @return unknown
	 */
	public function getCustomerData($customer, $action) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$data = array();
		try {
			switch($action) {
				case self::LOGIN :
					$data = $this->getCustomerSessionId($customer);
					$data['login_date'] = $this->_date->date('Y-m-d');
					break;
				case self::LOGOUT :
					$data = array();
					$sessionGeneric = $objectManager->get('Magento\Framework\Session\Generic');
					$data['session_id'] = $sessionGeneric->getCustomerSessionId();
					$data['logout_date'] = $this->_date->date('Y-m-d');
					$sessionGeneric->unsTrackingSessionId();
					$sessionGeneric->unsCustomerSessionId();
					break;
				case self::CREATE_ACCOUNT :
					$data = $this->getCustomerSessionId($customer);
					$data['firstname'] = $customer->getFirstname();
					$data['lastname'] = $customer->getLastname();
					$data['subscription_status'] = $this->getSubscriptionStatus($customer->getId());
					$data['account_created'] = $this->_date->date('Y-m-d');
					break;
			}
			$customer_data =  $objectManager->create('Magento\Customer\Model\Customer')->load($customer->getId());
			$data['user_id'] = $customer->getId();
			$data['user_name'] = $customer_data->getFirstname().' '.$customer_data->getLastname();
			$data['user_mail'] = $customer_data->getEmail();
			$data['timestamp'] = strtotime($this->_date->date('Y-m-d'));
			$data['ip_address'] = $this->_remoteAddress->getRemoteAddress();
		} catch (\Exception $e) {
			$this->logger->critical($e);
		}
		return $data;
	}
	
	/**
	 * Get the customer subscription status
	 *
	 * @param unknown $customerId   	
	 * @return string
	 */
	public function getSubscriptionStatus($customerId) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$subscriberFactory = $objectManager->create('Magento\Newsletter\Model\Subscriber')->loadByCustomerId($customerId);
		if ($subscriberFactory->getSubscriberStatus () == self::STATUS_UNSUBSCRIBED) {
			$status = 'Unsubscribed';
		} elseif ($subscriberFactory->getSubscriberStatus () == self::STATUS_SUBSCRIBED) {
			$status = 'Subscribed';
		} elseif ($subscriberFactory->getSubscriberStatus () == self::STATUS_UNCONFIRMED) {
			$status = 'Unconfirmed';
		} elseif ($subscriberFactory->getSubscriberStatus () == self::STATUS_NOT_ACTIVE) {
			$status = 'Not Activated';
		} else {
			$status = '';
		}
		return $status;
	}
	
	/**
	 * Get the customer session info
	 *
	 * @param unknown $customerId        	
	 * @return string
	 */
	public function getCustomerSessionId($customer) {
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();			
		$coreSession = $objectmanager->create('Magento\Framework\Session\SessionManagerInterface');
		$data = array();
		
		$visitor_data    = $coreSession->getVisitorData();
            	$sesstionId = $visitor_data['session_id'] ? $visitor_data['session_id'] : $visitor_data['session_id'] . strtotime ( date ( 'Y-m-d H:i:s' ) );
		$session = $sesstionId;

		$data ['session_id'] = $session;
		$data ['previous_session_id'] = $this->_cookieManager->getCookie ( 'targetbay_session_id' );
		$coreSession->setCustomerSessionId ( $session );
		return $data;
	}
	
	/**
	 * Remove the cookies
	*/ 
	public function removeCookies() {
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();			
		$_cookieManagerInterface = $objectmanager->create('\Magento\Framework\Stdlib\CookieManagerInterface');		
		$_cookieMetadata = $objectmanager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');		
		$coreSession = $objectmanager->create('Magento\Framework\Session\SessionManagerInterface');
		$metadata = $_cookieMetadata->createPublicCookieMetadata()
				  ->setDuration(self::TIMEOUT)
				  ->setPath('/')
			  	  ->setDomain($coreSession->getCookieDomain())
            			  ->setHttpOnly(false);

		$_cookieManagerInterface->deleteCookie('trackingsession',$metadata);
		$_cookieManagerInterface->deleteCookie('targetbay_session_id',$metadata);
		$_cookieManagerInterface->deleteCookie('trackingid',$metadata);
		$_cookieManagerInterface->deleteCookie('trackingemail',$metadata);
		$_cookieManagerInterface->deleteCookie('trackingname',$metadata);
		$_cookieManagerInterface->deleteCookie('trackingorderid',$metadata);
		$_cookieManagerInterface->deleteCookie('utm_source',$metadata);
		$_cookieManagerInterface->deleteCookie('utm_token',$metadata);
		$_cookieManagerInterface->deleteCookie('user_loggedin',$metadata);
	}
	
	/**
	 * Get the Order data
	 *
	 * @param unknown $object        	
	 * @return string
	 */
	public function getInfo($object) {
		$customer = $object->getCustomer ();
		$items = $object->getAllVisibleItems ();
		$data = $this->getSessionInfo ( $object );

		if($object->getCustomerIsGuest()) {
			$billingAddress = $object->getBillingAddress();
			$data ['first_name'] = $billingAddress->getFirstname();
			$data ['last_name'] = $billingAddress->getLastname();
			$guestUsername = $data ['first_name'].' '.$data ['last_name'];
		} else {
			$data ['first_name'] = $object->getCustomerFirstname();
			$data ['last_name'] = $object->getCustomerLastname();
			$guestUsername = '';
		}
		
		$gName = !empty($guestUsername) ? $guestUsername : self::ANONYMOUS_USER;
		$data ['user_name'] = $object->getCustomerIsGuest () ? $gName : $data ['first_name'] . ' ' . $data ['last_name'];
		$data ['user_mail'] = $object->getCustomerEmail ();
		$data ['order_id'] = $object->getId ();
		$data ['order_price'] = $object->getSubtotal ();
		$data ['order_quantity'] = $object->getData ( 'total_qty_ordered' );
		$data ['shipping_method'] = $object->getData ( 'shipping_description' );
		$data ['shipping_price'] = $object->getData ( 'shipping_amount' );
		$data ['tax_amount'] = $object->getData ( 'tax_amount' );
		$data ['payment_title'] = $object->getPayment()->getMethodInstance()->getTitle();
		return $data;
	}
	
	/**
	 * Get the product info
	 *
	 * @param unknown $product        	
	 * @return string
	 */
	public function getProductData($product) {
		$data = array();
		$data ['image_url'][] = $this->getProductImages ( $product );
		$data ['entity_id'] = $product->getId ();
		$data ['attribute_set_id'] = $product->getEntityTypeId ();
		$data ['type_id'] = $product->getTypeId ();
		$data ['sku'] = $product->getSku ();
		$data ['product_status'] = $product->getStatus ();
		$data ['currency_type'] = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
		
		$data ['stock_count'] = - 1;
		
		if ($stock = $product->getData ( 'stock_data' )) {
			$data ['stock_count'] = ! empty ( $stock ['is_in_stock'] ) ? $stock ['qty'] : - 1;
		}
		
		$data ['visibility'] = $product->getVisibility ();
		$data ['description'] = $product->getDescription ();
		$data ['msrp_price'] =  $product->getMsrp();
		$data ['price'] = $product->getPrice ();
		$data ['weight'] = $product->getWeight ();
		$data ['name'] = $product->getName ();
		
		$data ['category'] = $this->getProductCategory($product);
		$data ['url_key'] = $product->getUrlKey ();
		$data ['full_url_key'] = $product->getProductUrl ();
		
		$configOptions = array ();
		$customOptions = array ();
		
		if ($configData = $product->getData ( 'configurable_attributes_data' )) {
			$this->debug('11');
			$configOptions = $this->productOptions ( $configData, 'label' );
		}
		if ($custOptions = $product->getData ( 'product_options' )) {
			$this->debug('22');
			$customOptions = $this->productOptions ( $custOptions );
		}
		$options = array_merge ( $configOptions, $customOptions );
		if (! empty ( $options ))
			$data ['attributes'] = $options;
		return $data;
	}
	
	/**
	 * Get the product options when saving product
	 *
	 * @param unknown $configData        	
	 * @param string $customOption        	
	 * @return multitype:multitype:unknown
	 */
	public function productOptions($configData, $customOption = 'title') {
		$options = array ();
		foreach ( $configData as $cdata ) {
			$attrLabel = $cdata [$customOption];
			if (! isset ( $cdata ['values'] )) {
				$options [] = array (
						'label' => $attrLabel,
						'value' => $attrLabel 
				);
				continue;
			}
			foreach ($cdata['values'] as $val) {
				if (!isset($cdata [$customOption])) {
					$attrVal = $val [$customOption];
					$options [] = array (
							'label' => $attrLabel,
							'value' => $attrVal 
					);
				}
			}
		}
		return $options;
	}
	
	/**
	 * Get the product images
	 *
	 * @param unknown $product        	
	 * @return string
	 */
	public function getProductImages($product) {
		$images = array();
		$images ['url'] = $this->getImageUrl ( $product, 'image' );
		$images ['position'] = 1;
		$images ['thumbnail_image'] = $this->getImageUrl ( $product, 'small_image' );
		$images ['medium_image'] = $this->getImageUrl ( $product, 'thumbnail' );
		return $images;
	}
	
	/**
	 * Get the wishlist product info
	 *
	 * @param unknown $productId        	
	 * @return string
	 */
	public function getWishlistProductInfo($productId) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
		$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');

		$data = array();		
		$qty = 1;

	 	$data['type']         = $product->getTypeId();
		$data['product_id']   = $product->getId();
		$data['product_sku']  = $product->getSku();
		$data['name'] = $objectManager->get('Magento\Framework\Escaper')->escapeQuote($product->getName ());
		$data['msrp_price'] = $product->getMsrp();
		$data['price'] = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
		$data['special_price'] = $product->getSpecialPrice();
		$data['productimg']   = $this->getImageUrl($product, 'image');        
		$data['category']      = $this->getProductCategory($product);
		$data['abstract'] = $product->getAbstract();
		$data['category_name'] = $this->getProductCategoryName($product);
		$data['quantity']      = ($requestInterface->getParam('qty')) ? $requestInterface->getParam('qty') : $qty;
		$data['page_url']      = $product->getUrlModel()->getUrl($product);

		return $data;
	}

	public function getQuoteItemsInfo($quoteId) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('\Magento\Quote\Model\QuoteFactory')->load($productId);
	}
	
	/**
	 * Get the quote item info
	 *
	 * @param unknown $items        	
	 * @return string
	 */
	public function getQuoteItems($items, $orderExportApi = false) {
		$dataItems = array ();
		foreach($items as $orderItem) {
			$dataItem = $this->getItemInfo($orderItem);
			if ($customOptions = $this->getCustomOptionsInfo($orderItem, $orderExportApi))
				$dataItem['attributes'] = $customOptions;
			$dataItems[] = $dataItem;
		}
		
		return $dataItems;
	}
	
	/**
	 * Collect the wishlist items info
	 *
	 * @param unknown $productIds        	
	 * @return multitype:unknown
	 */
	public function getWishlistItemsInfo($wishlistInfo) {

		$dataItems = array();
		$wishlistItemCollection = $wishlistInfo->setStoreId(1)->getItemCollection();

		foreach($wishlistItemCollection as $id => $wishlistItem) {
			$product = $wishlistItem->getProduct();
			$dataItem = $this->wishlistProductInfo($product);
			$dataItems[$id] = $dataItem;
		}
		return $dataItems;
	}
	
	/**
	 * Collect the wishlist items product info
	 *
	 * @param unknown $productIds        	
	 * @return multitype:unknown
	 */	
	public function wishlistProductInfo($product) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load ($product->getData ( 'entity_id' ));

		$dataItem = array();
		$dataItem ['type'] = $product->getTypeId ();
		$dataItem ['product_id'] = $product->getId ();
		$dataItem ['product_name'] = $objectManager->get('Magento\Framework\Escaper')->escapeQuote($product->getName ());
		$dataItem ['msrp_price'] =  $product->getMsrp();
		$dataItem ['price'] = $product->getPrice ();
		$dataItem ['productimg'] = $this->getImageUrl ( $product, 'image' );
		$dataItem ['abstract'] = $product->getAbstract();

		$dataItem ['category'] = $this->getProductCategory($product);
		$dataItem ['category_name'] = $this->getProductCategoryName($product);
		$dataItem ['page_url'] = $product->getUrlModel()->getUrl($product);
		return $dataItem;
	
	}
	
	/**
	 * Retrieve generated code
	 *
	 * @return string
	 */
	public function generateCode()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$format = $this->getCouponCodeFormat();
		$length = $this->getCouponCodeLength();
		if (empty($format)) {
		    $format = \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC;
		} else {		
		$charset = $objectManager->get('Magento\SalesRule\Helper\Coupon')->getCharset ($format);
		}

		$code = '';
		$charsetSize = count($charset);
		$split = max(0, (int)$this->getDash());
		$length = max(1, (int)$length);
		for ($i = 0; $i < $length; ++$i) {
		    $char = $charset[\Magento\Framework\Math\Random::getRandomNumber(0, $charsetSize - 1)];
		    if (($split > 0) && (($i % $split) === 0) && ($i !== 0)) {
			$char = $splitChar . $char;
		    }
		    $code .= $char;
		}

		return $this->getPrefix() . $code . $this->getSuffix();
	}
	
	/**
	 * Get API url for static pages
	 *
	 * @return string
	 */
	public function getApiUrl()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');

		$controllername = $requestInterface->getControllerName();
		$moduleName     = $requestInterface->getModuleName(); 
		$endPointUrl    = '';
		$type           = '';

		if ($moduleName == 'cms' || $moduleName == 'brand') {
		    $type        = 'page-visit';
		    $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
		} elseif ($controllername == 'category') {
		    $type        = 'category-view';
		    $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
		} elseif ($controllername == 'product') {
		    $type        = 'product-view';
		    $endPointUrl = $this->getHostname() . $type . '?api_token=' . $this->getApiToken();
		}

		return $endPointUrl;
	}
	
	/**
	 * Get page visit info for static page
	 *
	 * @return string
	 */
	public function getPageInfo()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');

		$controllername = $requestInterface->getControllerName();
		$moduleName     = $requestInterface->getModuleName();

		if ($moduleName == 'cms' || $moduleName == 'brand') {
		    $data = $this->getPageVisitData();
		} elseif ($controllername == 'category') {
		    $data = $this->getCategoryViewData();
		} elseif ($controllername == 'product') {
		    $data = $this->getProductViewData();
		}
		$data['index_name'] = $this->getApiIndex();

		return $data;
	}
    
	/**
	 * Visiting page info	
	 *
	 * @return void
	 */
	public function getPageVisitData()
	{
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance();			
		$eventHandle = $objectmanager->get('Targetbay\Tracking\Observer\EventHandles');
		$eventHandle->setCookieValues();

		// Page Visit Tracking
		$data = $this->visitInfo();
		return $data;
	}
    
	/**
	 * Category view page
	 *
	 * @param Varien_Event_Observer $observer        	
	 *
	 * @return void
	 */
	public function getCategoryViewData()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$registry = $objectManager->get('\Magento\Framework\Registry');

		$category              = $registry->registry('current_category');
		$data                  = $this->visitInfo();
		$data['category_id']   = $category->getId();
		$data['category_url']  = $category->getUrl();
		$data['category_name'] = $objectManager->get('Magento\Framework\Escaper')->escapeQuote($category->getName());

		return $data;
	}

	/**
	 * Product view page
	 *
	 * @param Varien_Event_Observer $observer        	
	 *
	 * @return void
	 */
	public function getProductViewData()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$registry = $objectManager->get('\Magento\Framework\Registry');

		// Get the base visit info
		$data        = $this->visitInfo();
		$productData     = $registry->registry('product');
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productData->getId());
		$categoryIds = $product->getCategoryIds();

		if (count($categoryIds)) {
		    $firstCategoryId  = $categoryIds[0];
		    $_category = $objectManager->get('Magento\Catalog\Model\Category')->load($firstCategoryId);
		    $data['category'] = $objectManager->get('Magento\Framework\Escaper')->escapeQuote($_category->getName());
		}
		$data['product_id']   = $product->getId();
		$data['product_name'] = $objectManager->get('Magento\Framework\Escaper')->escapeQuote($product->getName());
		$data['msrp_price'] = $product->getMsrp();
		$data['price'] = $product->getPrice();
		$data['productimg']   = $this->getImageUrl($product, 'image');
		$data['abstract'] = $product->getAbstract();

		$data['stock'] = self::OUT_OF_STOCK;
		$stock = $product->getStockItem();
		if ($product->isAvailable()) {
			$data['stock'] = self::IN_STOCK;
		}

		return $data;
	}
    
	/**
	 * Get the targetbay review count and rating for product
	 *      	
	 * @return array
	 */ 
	public function getRichSnippets()
	{
		$responseData = ''; 
		try {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$registry = $objectManager->get('\Magento\Framework\Registry');
			$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
			$controllername = $requestInterface->getControllerName();
			$moduleName     = $requestInterface->getModuleName();

			$data = array();
			$type = self::RATINGS_STATS;
			$apiToken = '?api_token=' . $this->getApiToken();
			$feedUrl = $this->getHostname() . $type . $apiToken;
			$data['index_name'] = $this->getApiIndex();
			if($moduleName == 'catalog' && $controllername == 'product') {
				$productData     = $registry->registry('product');
				$productId = $productData->getId();
				//$productId = 104970;
				$data['product_id'] = $productId;
			}

			$jsondata = json_encode($data);	
			$response = $this->postPageInfo($feedUrl, $jsondata);
			$body = json_decode($response);
			if(!empty($body)) {
				$averageScore = $body->reviews_average;
				$reviewsCount = $body->reviews_count;
				$reviewsDetails = $body->reviews;
				$responseData = array( "average_score" => $averageScore, "reviews_count" => $reviewsCount, "reviews" => $reviewsDetails);
			}
			return $responseData;
		} catch (\Exception $e) {
			$this->debug('Error :'.$e);
		}
		return array();
	}
    
	/**
	 * Get the targetbay review count and dynamic ids
	 *      	
	 * @return array
	 */ 
	public function getTargetbayReviewId()
	{
		try {
			$trackingSnippet = $this->getRichSnippets();
			$itemRefData = array();
			if($trackingSnippet['reviews_count'] > 0) {
				foreach($trackingSnippet['reviews'] as $key => $aggregateReviewDetails) {
					$itemRefData[]= 'tb-review-'.$key;
				}
				$itemRef = implode(' ', $itemRefData);
			}
			return $itemRef;
		} catch(Exception $e) {
			$this->debug('Error :'.$e);
		}
		return array();
	}
    
	/**
	 * Get the targetbay review count and rating for product
	 *      	
	 * @return array
	 */ 
	public function getSiteReviewSnippets()
	{ 
		$responseData = ''; 
		try {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$registry = $objectManager->get('\Magento\Framework\Registry');
			$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
			$controllername = $requestInterface->getControllerName();
			$moduleName     = $requestInterface->getModuleName();
			$data = array();
			$type = self::RATINGS_STATS;
			$apiToken = '?api_token=' . $this->getApiToken();
			$feedUrl = $this->getHostname() . $type . $apiToken;
			$data['index_name'] = $this->getApiIndex();

			$jsondata = json_encode($data);	
			$response = $this->postPageInfo($feedUrl, $jsondata);

			$body = json_decode($response);
			if(!empty($body)) {
			$averageScore = $body->reviews_average;
			$reviewsCount = $body->reviews_count;
			$reviewsDetails = $body->reviews;
			$responseData = array( "average_score" => $averageScore, "reviews_count" => $reviewsCount, "reviews" => $reviewsDetails);
			}
			return $responseData;
		} catch (\Exception $e) {
			$this->debug('Error :'.$e);
		}
		return array();
	}
    
	/**
	 * Get the targetbay questions for product
	 *      	
	 * @return array
	 */ 
	public function getQuestionSnippets()
	{
		$responseData = ''; 
		try {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$registry = $objectManager->get('\Magento\Framework\Registry');
			$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
			$controllername = $requestInterface->getControllerName();
			$moduleName     = $requestInterface->getModuleName();

			$data = array();
			$type = self::QUESTION_STATS;
			$apiToken = '?api_token=' . $this->getApiToken();
			$feedUrl = $this->getHostname() . $type . $apiToken;
			$data['index_name'] = $this->getApiIndex();
			if($moduleName == 'catalog' && $controllername == 'product') {
				$productData     = $registry->registry('product');
				$productId = $productData->getId();
				$data['product_id'] = $productId;
			}

			$jsondata = json_encode($data);	
			$response = $this->postPageInfo($feedUrl, $jsondata);
			$body = json_decode($response);
			if(!empty($body)) {
                $qaCount = $body->qa_count;
                $qaDetails = $body->qas;
                $qaAuthor = $body->client;
                $responseData = array("qa_count" => $qaCount, "qa_details" => $qaDetails, "qa_author" => $qaAuthor);
                return $responseData;
			}
			return $responseData;
		} catch (\Exception $e) {
			$this->debug('Error :'.$e);
		}
		return array();
	}
}
