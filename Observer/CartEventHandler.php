<?php
namespace Targetbay\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CartEventHandler implements ObserverInterface
{
    CONST ANONYMOUS_USER = 'anonymous';
    CONST ALL_PAGES = 'all';
    CONST PAGE_VISIT = 'page-visit';
    CONST PAGE_REFERRAL = 'referrer';
    CONST CHECKOUT = 'checkout';

    protected $_trackingHelper;
    protected $_cookieManager;
    protected $_coreSession;

    public function __construct(
		\Targetbay\Tracking\Helper\Data $trackingHelper, 
                \Magento\Framework\Stdlib\CookieManagerInterface $_cookieManager,
	        \Magento\Framework\Session\Generic $_coreSession
    ){
	$this->_trackingHelper  = $trackingHelper;
	$this->_coreSession = $_coreSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
	$quoteId = $this->_coreSession->getRestoreQuoteId();
	$abandonedMail = $this->_coreSession->getAbandonedMail();
	if($abandonedMail && $quoteId != '') {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();	
		$customerSession = $objectManager->get('\Magento\Customer\Model\Session');	

		if($customerSession->isLoggedIn()) {
			return false;
		}

		$quote = $objectManager->get('Magento\Quote\Model\Quote')->load($quoteId);
		$cart = $objectManager->get('Magento\Checkout\Model\Cart');
		$price = 0;

		$quoteItems = $quote->getAllVisibleItems();

		foreach($quoteItems as $item) {
			$item->setCustomPrice($price);
			$item->getProduct()->setIsSuperMode(true);
		}
		$cart->saveQuote();
		$cart->save();
	}
	return;
    }
}
