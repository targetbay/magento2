<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CreateOrderEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST ORDER_ITEMS = 'ordered-items';

    // order fullfillment process
    CONST ORDER_SHIPMENT = 'shipment';

    protected $_request;
    protected $_trackingHelper;
    protected $_cookieManager;
    protected $_coreSession;
    private $apiToken;
    private $indexName;
    private $targetbayHost;
    private $logger;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper,
		\Magento\Framework\App\RequestInterface $request, 
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\Registry $registry
    ){
	$this->_trackingHelper  = $trackingHelper;
        $this->_request = $request;
        $this->_registry = $registry;
	$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken ();
	$this->_indexName       = $this->_trackingHelper->getApiIndex ();
	$this->_targetbayHost   = $this->_trackingHelper->getHostname ();
        $this->_cookieManager = $_cookieManager;
	$this->_coreSession = $_coreSession;
	$this->_logger = $logger;
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
		$res = $this->_trackingHelper->postPageInfo( $endPointUrl, json_encode ($data));
	} catch (\Exception $e) {
		$this->_logger->critical($e);
	}
    }
	
    /**
     * Order data
     *
     * @param \Magento\Framework\Event\Observer $observer  
     *
     * @return void|boolean
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$this->_trackingHelper->debug ( "create order");
	$orderInfo = array ();

	if (!$this->_registry->registry('order_pushed')) {

		$this->_registry->register( 'order_pushed', true );
		if (!$this->_trackingHelper->canTrackPages(self::ORDER_ITEMS)) {
			return false;
		}

		try {
			$order = $observer->getEvent()->getOrder(); 
			$params = $this->_request->getParams();

			$order_id = $order->getIncrementId();
			$order_details = $objectManager->get('Magento\Sales\Model\Order');
			$order_information = $order_details->loadByIncrementId($order_id);

			if ($this->pushShipmentData($order_information, $params))
				return false; // order shipment process so no need to make order submit api.
							      
			// Captute the customer registration.
			if ($customer = $this->_trackingHelper->isRegisterCheckout($order)) {
				$this->pushRegisterData($customer);
			}

			// Order Data Push to the Tag Manager
			$orderInfo = $this->_trackingHelper->getInfo($order);
			$orderInfo['cart_items'] = $this->_trackingHelper->getOrderItemsInfo($order);
			$this->pushPages($orderInfo, self::ORDER_ITEMS);
		} catch (\Exception $e) {
			$this->_logger->critical($e);
		}
	}
	return;
    }
	
    /**
     * Push the shipment data
     *
     * @param unknown $order        	
     * @param unknown $params        	
     * @return boolean
     */
    public function pushShipmentData($order, $params) {
	if ($this->_trackingHelper->isFullFillmentProcess($params)) {
		try {
			$data = $this->_trackingHelper->getFullFillmentData($order, $params);
			$this->pushPages($data, self::ORDER_SHIPMENT);
			return true;
		} catch (\Exception $e) {
			$this->_logger->critical($e);
		}
	}
	
	return false;
    }
	
    /**
     * Push the registration data
     *
     * @param unknown $customer        	
     *
     * @return void|boolean
     */
    public function pushRegisterData($customer) {
	if (! $this->_trackingHelper->canTrackPages(self::CREATE_ACCOUNT)) {
		return false;
	}
	try {
		$data = $this->_trackingHelper->getCustomerData($customer, self::CREATE_ACCOUNT);
		$this->pushPages($data, self::CREATE_ACCOUNT);
	} catch (\Exception $e) {
		$this->_logger->critical($e);
	}
	return;
    }
}
