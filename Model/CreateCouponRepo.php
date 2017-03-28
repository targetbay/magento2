<?php

/**
 * @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */ 

namespace Targetbay\Tracking\Model;

use Targetbay\Tracking\Api\CreateCouponInterface;

/**
 * Defines the implementaiton class of the CreateCouponInterface.
 */
class CreateCouponRepo implements CreateCouponInterface
{
	/**
	 * @var \Targetbay\Tracking\Helper\Data $_trackingHelper
	 */
	protected $_trackingHelper;

	/**
	 * @var \Magento\Framework\App\RequestInterface $_request
	 */
	protected $_request;

	/**
	 * @param \Targetbay\Tracking\Helper\Data $trackingHelper
	 */
	public function __construct(
		\Magento\Framework\App\RequestInterface $_request,
		\Targetbay\Tracking\Helper\Data $trackingHelper
	)
	{
		$this->_request = $_request;
		$this->_trackingHelper  = $trackingHelper;
	}

	/**
     	 * create coupon code.
	 * @throws \Exception
	 * @return int
     	 */
	public function createcoupon() {
		$times_used = $this->_request->getParam ( 'no_times' );
		$expiry_days = $this->_request->getParam ( 'expiry_date' );
		$expiry_hrs = $this->_request->getParam ( 'expiry_hrs' );
	}
}
