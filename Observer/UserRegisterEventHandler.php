<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class UserRegisterEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
	
    CONST CREATE_ACCOUNT = 'create-account';

    protected $_productRepository;
    protected $_cart;
    protected $formKey;
    protected $_trackingHelper;
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
		\Magento\Framework\Data\Form\FormKey $formKey
    ){
	$this->_trackingHelper  = $trackingHelper;
        $this->_productRepository = $productRepository;
        $this->_cart = $cart;
        $this->formKey = $formKey;
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
    * Registration observer
    *
    * @param \Magento\Framework\Event\Observer $observer        	
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {	
	$this->pushRegisterData ( $observer->getCustomer () );
    }
	
    /**
     * Push the registration data
     *
     * @param unknown $customer        	
     *
     * @return void|boolean
     */
    public function pushRegisterData($customer) 
    {
	if (! $this->_trackingHelper->canTrackPages ( self::CREATE_ACCOUNT )) {
		return false;
	}
	$data = $this->_trackingHelper->getCustomerData ( $customer, self::CREATE_ACCOUNT );
	$this->pushPages ( $data, self::CREATE_ACCOUNT );
	
	return;
    }
}
