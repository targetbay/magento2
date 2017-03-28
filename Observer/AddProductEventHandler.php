<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class AddProductEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST ADD_PRODUCT = 'add-product';
    CONST UPDATE_PRODUCT = 'update-product';

    protected $_productRepository;
    protected $formKey;
    protected $_request;
    protected $_trackingHelper;
    protected $_cookieManager;
    protected $_coreSession;

    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		RequestInterface $_request,
		\Targetbay\Tracking\Helper\Data $trackingHelper,
		\Magento\Catalog\Model\ProductRepository $productRepository, 
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Magento\Framework\Data\Form\FormKey $formKey
    ){
	$this->_request = $_request;
	$this->_trackingHelper  = $trackingHelper;
        $this->_productRepository = $productRepository;
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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	if (! $this->_trackingHelper->canTrackPages ( self::ADD_PRODUCT )) {
		return false;
	}

	$params = $this->_request->getParams();
	$product = $observer->getEvent ()->getProduct ();

	if ($product->getId ()) {
		$type = self::ADD_PRODUCT;
		if ($this->_request->getParam('id')) {
			if (! $this->_trackingHelper->canTrackPages ( self::UPDATE_PRODUCT )) {
				return false;
			}
			$type = self::UPDATE_PRODUCT;
		}
		$data = $this->_trackingHelper->getProductData ( $product );
		$this->pushPages ( $data, $type );
	}
	
	return;
    }
}
