<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class AddCartEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST ADDTOCART = 'add-to-cart';

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

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	if (! $this->_trackingHelper->canTrackPages ( self::ADDTOCART )) {
		return false;
	}
        //$item = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product');

	//if ($item->getParentItem ()) {
		//$item = $item->getParentItem ();
	//}

	$productEventInfo = $observer->getEvent()->getData('product');
	
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$quote = $objectManager->get('Magento\Checkout\Model\Cart')->getQuote();
	$item = $quote->getItemByProduct( $productEventInfo );

	$data = array_merge ( $this->_trackingHelper->getCartInfo(), $this->_trackingHelper->getItemInfo( $item, self::ADDTOCART ) );

	$data ['price'] = $item->getProduct()->getFinalPrice();

	if ($customOptions = $this->_trackingHelper->getCustomOptionsInfo ( $item, null )) {
		$data ['attributes'] = $customOptions;
	}
	$this->pushPages ( $data, self::ADDTOCART );
	return;
    }
}
