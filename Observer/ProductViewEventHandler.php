<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class ProductViewEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST PRODUCT_VIEW = 'product-view';

    // product stock status
    CONST IN_STOCK = 'in-stock';
    CONST OUT_OF_STOCK = 'out-stock';

    protected $_productRepository;
    protected $_trackingHelper;
    protected $_registry;
    protected $_cookieManager;
    protected $_coreSession;

    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper,
		\Magento\Catalog\Model\ProductRepository $productRepository, 
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession,
		\Magento\Framework\Registry $registry
    ){
	$this->_trackingHelper  = $trackingHelper;
        $this->_productRepository = $productRepository;
        $this->_registry = $registry;
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
	if (! $this->_trackingHelper->canTrackPages ( self::PRODUCT_VIEW )) {
		return false;
	}
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
	// Get the base visit info
	$data = $this->_trackingHelper->visitInfo ();
	$product = $this->_registry->registry ( 'product' );
	$data ['category'] = $this->_trackingHelper->getProductCategory ($product);
	$data ['product_id'] = $product->getId ();
	$data ['product_name'] = $product->getName ();
	$data ['msrp_price'] = $priceHelper->currency($product->getMsrp(), true, false);
	$data ['price'] = $product->getPrice ();
	$data ['productimg'] = $this->_trackingHelper->getImageUrl ($product, 'image');
	$data ['stock'] = self::OUT_OF_STOCK;
	$stock = $product->getStockItem ();
	if ($product->isAvailable ()) {
		$data ['stock'] = self::IN_STOCK;
	}

	$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
	$controllername = $requestInterface->getControllerName();
	if ($controllername == 'product')
		return false;

	$this->pushPages ( $data, self::PRODUCT_VIEW );
	
	return;
    }
}
