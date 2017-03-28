<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SaveAccountDataEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
	
    CONST CUSTOMER_ACCOUNT = 'change-user-account-info';

    protected $_trackingHelper;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_date;

    private $_apiToken;
    private $_indexName;
    private $_targetbayHost;

    public function __construct(
	        \Magento\Framework\Stdlib\DateTime\DateTime $_date,
	        \Magento\Checkout\Model\Session $_checkoutSession,
	        \Magento\Customer\Model\Session $_customerSession,
		\Targetbay\Tracking\Helper\Data $_trackingHelper
    ){
	$this->_trackingHelper  = $_trackingHelper;
	$this->_checkoutSession = $_checkoutSession;
	$this->_customerSession = $_customerSession;
	$this->_date = $_date;
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
	$data ['index_name'] = $this->_indexName;
	try {
		$res = $this->_trackingHelper->postPageInfo( $endPointUrl, json_encode($data));
	} catch (Exception $e) {
		$this->_trackingHelper->debug(" '$type' ERROR:" . $e->getMessage());
	}
    }
	
   /**
    * Update account info observer
    *
    * @param \Magento\Framework\Event\Observer $observer        	
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();			
        if (!$this->_trackingHelper->canTrackPages(self::CUSTOMER_ACCOUNT)) {
            return false;
        }
	$this->_checkoutSession->setTitle('Account Information');
        $data = $this->_trackingHelper->visitInfo();
	$customerId = $this->_customerSession->getCustomerId();
	$customerObj = $objectManager->get('Magento\Customer\Model\Customer');
	$data['customer_id'] = $customerId;
	$data['firstname'] = $customerObj->getFirstname();
	$data['lastname'] = $customerObj->getLastname();
	$data['email'] = $customerObj->getEmail();
        $data['account_updated'] = $this->_date->date('Y-m-d');
        $this->pushPages($data, self::CUSTOMER_ACCOUNT);
        return;
    }
}
