<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class AdminConfirmEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';

    protected $_trackingHelper;
    private $apiToken;
    private $indexName;
    private $targetbayHost;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper
    ){
	$this->_trackingHelper  = $trackingHelper;
	$this->_apiToken        = '?api_token=' . $this->_trackingHelper->getApiToken ();
	$this->_indexName       = $this->_trackingHelper->getApiIndex ();
	$this->_targetbayHost   = $this->_trackingHelper->getHostname ();
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
	$this->_trackingHelper->debug ( 'admin account confirmation' );
	$this->_trackingHelper->debug ( print_r($observer->getCustomer ()->getData(), true) );
	$customer_info = $observer->getCustomer ()->getData();
	$data = array_merge ( $this->_trackingHelper->visitInfo (), $customer_info );
	$this->pushPages ( $data, self::ADMIN_ACTIVATE_ACCOUNT );
	
	return;
    }
}
