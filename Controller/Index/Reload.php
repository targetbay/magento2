<?php
namespace Targetbay\Tracking\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
 
class Reload extends \Magento\Framework\App\Action\Action
{
	CONST TIMEOUT = 900;
	
    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */ 
    public function __construct(Context $context,
				\Magento\Checkout\Model\Session $checkoutSession			
				)
    {
        parent::__construct(
		$context,
		$checkoutSession
	);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Reload product to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */ 
    public function execute()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();	
		$customerSession = $objectManager->get('\Magento\Customer\Model\Session');	
		$coreSession = $objectManager->get('\Magento\Core\Model\Session');
		$helper = $objectManager->get('Targetbay\Tracking\Helper\Data');
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

		$cookieMetadata = $objectmanager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory');
		$cookieManager = $objectmanager->get('Magento\Framework\Stdlib\CookieManagerInterface');


		$utmSource = $this->getRequest()->getParam('utm_source');
		$utmToken = $this->getRequest()->getParam('token');

		$quoteId = (int)$this->getRequest()->getParam('quote_id');
		$guestUserId = $this->getRequest()->getParam('guest_user_id');
		$resultRedirect->setPath('checkout/cart', ['utm_source' => $utmSource, 'token' => $utmToken]);		

		$metadata = $cookieMetadata
					  ->createPublicCookieMetadata()
					  ->setDuration(self::TIMEOUT)
					  ->setPath('/')
					  ->setDomain($coreSession->getCookieDomain())
	            	  ->setHttpOnly(false);

		if($guestUserId != '' && !$customerSession->isLoggedIn()) {
			$cookieManager->setPublicCookie('targetbay_session_id', $guestUserId, $metadata);
		}

		if($customerSession->isLoggedIn()) {
			return $resultRedirect;
		}

		try {
			$quoteIdMaskFactory = $objectManager->get('\Magento\Quote\Model\QuoteIdMask');
			$quoteManagement = $objectManager->get('\Magento\Quote\Api\CartManagementInterface');
			$store_id = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
			$formKey =  $objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey();

			$coreSession = $objectManager->create('Magento\Framework\Session\SessionManagerInterface');
			$coreSession->setRestoreQuoteId($quoteId);
			$coreSession->setAbandonedMail(true);

		    if(empty($quoteId)) {
			   return $resultRedirect;
			}

			$quote = $objectManager->get('Magento\Quote\Model\Quote')->load($quoteId);
			if($quote->getId()) {
				$quote->setIsActive(1)
				      ->save();
				$this->_checkoutSession->replaceQuote($quote);
			}
		} catch(\Exception $e) {
			$helper->debug('Error:'.$e->getMessage());
	            	$objectManager->get('Psr\Log\LoggerInterface')->critical($e);
		}
		return $resultRedirect;
    }
}
