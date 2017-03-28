<?php
 
/** @author Targetbay Team 
 * @copyright Copyright (c) 2016 Targetbay 
 * @package Targetbay_Tracking 
 */

namespace Targetbay\Tracking\Api;

use Targetbay\Tracking\Api\Data\PointInterface;

/**
 * Defines the save subscription interface. The function prototypes were therefore
 * selected to demonstrate different parameter and return values.
 */

interface UpdateSubscriptionInterface
{
	/**
     	 * save newsletter subscription.
     	 *
     	 * @return boolean.
     	 */
	public function updatesubscription();

}
