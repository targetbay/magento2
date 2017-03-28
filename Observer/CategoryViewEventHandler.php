<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CategoryViewEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST CATEGORY_VIEW = 'category-view';

    protected $_productRepository;
    protected $_trackingHelper;
    protected $_registry;
    protected $_cookieManager;
    protected $_session;

    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper,
		\Magento\Catalog\Model\ProductRepository $productRepository,  
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_session,
		\Magento\Framework\Registry $registry
    ){
	$this->_trackingHelper  = $trackingHelper;
        $this->_productRepository = $productRepository;
        $this->_registry = $registry;
	$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken ();
	$this->_indexName       = $this->_trackingHelper->getApiIndex ();
	$this->_targetbayHost   = $this->_trackingHelper->getHostname ();
        $this->_cookieManager = $_cookieManager;
	$this->_session = $_session;
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
	if (! $this->_trackingHelper->canTrackPages ( self::CATEGORY_VIEW )) {
		return false;
	}
	$category = $this->_registry->registry ( 'current_category', true );
	$data = $this->_trackingHelper->visitInfo ();
	$data ['category_id'] = $category->getId ();
	$data ['category_url'] = $category->getUrl ();
	$data ['category_name'] = $category->getName ();

	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
	$controllername = $requestInterface->getControllerName();
	if ($controllername == 'category')
		return false;

	$this->pushPages ( $data, self::CATEGORY_VIEW );
	
	return;
    }
}
